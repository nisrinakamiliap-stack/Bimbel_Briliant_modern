<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminRouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seluruh route aplikasi bernama dengan awalan tertentu.
     *
     * @return Collection<int, RoutingRoute>
     */
    private function routesNamed(string $prefix): Collection
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $route) => str_starts_with((string) $route->getName(), $prefix));
    }

    /**
     * Test setiap route admin dilindungi middleware auth dan role:admin di backend.
     */
    public function test_every_admin_route_requires_auth_and_admin_role(): void
    {
        $routes = $this->routesNamed('admin.');

        $this->assertGreaterThan(50, $routes->count());

        foreach ($routes as $route) {
            $middleware = $route->gatherMiddleware();

            $this->assertContains('auth', $middleware, "Route {$route->getName()} tidak memakai middleware auth.");
            $this->assertContains('role:admin', $middleware, "Route {$route->getName()} tidak memakai middleware role:admin.");
            $this->assertStringStartsWith('admin/', $route->uri(), "Route {$route->getName()} berada di luar prefix admin.");
        }
    }

    /**
     * Test route guru dan murid dilindungi sesuai role masing-masing.
     */
    public function test_teacher_and_student_routes_require_their_roles(): void
    {
        foreach (['teacher.' => 'role:teacher', 'student.' => 'role:student'] as $prefix => $role) {
            foreach ($this->routesNamed($prefix) as $route) {
                $middleware = $route->gatherMiddleware();

                $this->assertContains('auth', $middleware, "Route {$route->getName()} tidak memakai middleware auth.");
                $this->assertContains($role, $middleware, "Route {$route->getName()} tidak memakai middleware {$role}.");
            }
        }
    }

    /**
     * Test tidak ada kombinasi method dan URL yang terdaftar ganda.
     */
    public function test_there_are_no_duplicate_routes(): void
    {
        $signatures = collect(Route::getRoutes()->getRoutes())
            ->flatMap(fn (RoutingRoute $route) => collect($route->methods())->map(fn (string $method) => $method.' '.$route->uri()));

        $this->assertSame([], $signatures->duplicates()->values()->all());
    }

    /**
     * Test guru dan murid ditolak saat membuka seluruh halaman admin langsung melalui URL.
     */
    public function test_teacher_and_student_are_forbidden_from_every_admin_page(): void
    {
        $pages = $this->routesNamed('admin.')
            ->filter(fn (RoutingRoute $route) => in_array('GET', $route->methods(), true) && $route->parameterNames() === [])
            ->map(fn (RoutingRoute $route) => $route->getName());

        $this->assertContains('admin.reports.students', $pages);
        $this->assertContains('admin.settings.edit', $pages);

        foreach ([User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            foreach ($pages as $name) {
                $this->actingAs($user)->get(route($name))->assertForbidden();
            }
        }
    }

    /**
     * Test tamu diarahkan ke login saat membuka halaman admin.
     */
    public function test_guest_is_redirected_from_admin_pages(): void
    {
        foreach (['admin.dashboard', 'admin.reports.students', 'admin.settings.edit', 'admin.announcements.create'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    /**
     * Test admin dapat membuka seluruh halaman admin tanpa parameter dan setiap tautan sidebar tidak rusak.
     */
    public function test_admin_can_open_every_admin_page(): void
    {
        $admin = User::factory()->admin()->create();

        $pages = $this->routesNamed('admin.')
            ->filter(fn (RoutingRoute $route) => in_array('GET', $route->methods(), true) && $route->parameterNames() === [])
            ->map(fn (RoutingRoute $route) => $route->getName());

        foreach ($pages as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }

        foreach (['notifications.index', 'messages.index', 'messages.create'] as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }
    }
}
