<?php

use App\Http\Middleware\Auth\AuthenticateOrShowMessageMiddleware;
use App\Http\Middleware\Auth\EnsureEmailIsVerifiedMiddleware;
use App\Http\Middleware\Auth\EnsureUserIsAdminMiddleware;
use App\Http\Middleware\Context\RequestContextMiddleware;
use App\Http\Middleware\Localization\ApiLocalizationMiddleware;
use App\Http\Middleware\Localization\LocalizationMiddleware;
use App\Http\Middleware\PreventRequestsDuringMaintenanceMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        then: function (): void {
            Route::middleware(['web'])
                ->domain((string) parse_url((string) config('app.url'), PHP_URL_HOST))
                ->group(base_path('routes/web.php'));

            Route::middleware([
                'api',
                'throttle:api',
                RequestContextMiddleware::class,
                EnsureEmailIsVerifiedMiddleware::class,
            ])
                ->prefix(config('api.path'))
                ->domain(config('api.domain_name'))
                ->name('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware([
                'api',
                'throttle:api',
                RequestContextMiddleware::class,
                EnsureEmailIsVerifiedMiddleware::class,
                ApiLocalizationMiddleware::class,
            ])
                ->domain(config('api.domain_name'))
                ->prefix('{locale}-{country}')
                ->name('api-localized.')
                ->group(base_path('routes/api-localized.php'));

            Route::middleware(['web', LocalizationMiddleware::class])
                ->domain((string) parse_url((string) config('app.url'), PHP_URL_HOST))
                ->name('localized.')
                ->prefix('{locale}-{country:code}')
                ->group(base_path('routes/web-localized.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(
            PreventRequestsDuringMaintenance::class,
            PreventRequestsDuringMaintenanceMiddleware::class
        );
        $middleware->alias([
            'auth.or.message' => AuthenticateOrShowMessageMiddleware::class,
            'admin' => EnsureUserIsAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            if ($request->expectsJson()) {
                return true;
            }

            $apiDomain = config('api.domain_name');

            if ($apiDomain !== null && $request->getHost() === $apiDomain) {
                return true;
            }

            return $request->is('api') || $request->is('api/*');
        });

        $exceptions->context(fn (): array => ['user_id' => auth()->user()?->id]);
    })->create();
