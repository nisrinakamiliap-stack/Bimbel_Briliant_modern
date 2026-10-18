<?php

namespace App\Http\Middleware;

use App\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Membatasi akses route hanya untuk pengguna dengan role tertentu.
     *
     * Contoh pemakaian: `->middleware('role:admin')` atau `->middleware('role:admin,teacher')`.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowedRoles = array_map(fn (string $role): UserRole => UserRole::from($role), $roles);

        if ($user === null || ! $user->hasRole(...$allowedRoles)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
