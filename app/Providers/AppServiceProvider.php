<?php

namespace App\Providers;

use App\Services\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        $this->configureDefaults();

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Event::listen(
            Login::class,
            function ($event) {
                if ($event->user) {
                    AuditLogService::record(
                        'auth.login',
                        "Pengguna '{$event->user->name}' ({$event->user->email}) berhasil Login ke sistem.",
                        $event->user,
                        null,
                        null,
                        $event->user->id
                    );
                }
            }
        );

        Event::listen(
            Logout::class,
            function ($event) {
                if ($event->user) {
                    AuditLogService::record(
                        'auth.logout',
                        "Pengguna '{$event->user->name}' ({$event->user->email}) telah Logout.",
                        $event->user,
                        null,
                        null,
                        $event->user->id
                    );
                }
            }
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
