<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\AssignmentGrade;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\User;
use App\Observers\NotificationObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function (): Password {
            $rule = Password::min(8)->letters()->numbers();

            return $this->app->isProduction()
                ? $rule->mixedCase()->uncompromised()
                : $rule;
        });

        Route::bind('student', fn (string $value): User => User::query()->students()->findOrFail($value));
        Route::bind('teacher', fn (string $value): User => User::query()->teachers()->findOrFail($value));

        $this->registerNotificationEvents();
        $this->applySettings();
    }

    /**
     * Menerapkan zona waktu pilihan admin dan membagikan profil lembaga serta jumlah notifikasi/pesan
     * belum dibaca ke layout panel.
     */
    private function applySettings(): void
    {
        if (($timezone = Setting::timezone()) !== null) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        View::composer(['layouts.admin', 'layouts.portal', 'layouts.teacher', 'notifications.partials.tabs'], function ($view): void {
            $user = auth()->user();

            $view->with('institution', Setting::institution());
            $view->with('navCounts', $user === null ? [] : [
                'notifications' => $user->unreadNotifications()->count(),
                'messages' => $user->unreadConversationsCount(),
            ]);
        });
    }

    /**
     * Menghubungkan peristiwa model dengan notifikasi; dijalankan setelah transaksi database berhasil.
     */
    private function registerNotificationEvents(): void
    {
        $observer = new NotificationObserver;
        $afterCommit = fn (callable $callback): \Closure => fn ($model) => DB::afterCommit(fn () => $callback($model));

        User::created($afterCommit($observer->userCreated(...)));
        Enrollment::created($afterCommit($observer->enrollmentCreated(...)));
        Payment::created($afterCommit($observer->paymentCreated(...)));
        Assignment::saved($afterCommit($observer->assignmentSaved(...)));
        Schedule::created($afterCommit($observer->scheduleCreated(...)));
        Submission::created($afterCommit($observer->submissionCreated(...)));
        AssignmentGrade::saved($afterCommit($observer->gradeSaved(...)));
    }
}
