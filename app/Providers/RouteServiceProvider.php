<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    private const SENSITIVE_GUEST_API_ROUTES = [
        'api.requestFormPivot.post',
        'api.form.registration.post',
        'api.subform.response.store',
        'api.inventory.transactions.store.public',
        'api.guest.rental.vehicles.store',
        'api.guest.rental.venues.store',
        'api.event.participant.lookup.guest',
        'api.laboratory.equipments.check-in',
        'api.laboratory.equipments.check-out',
        'api.laboratory.equipments.update-end-use',
        'api.laboratory.equipments.report-location',
        'api.ict.equipments.check-in',
        'api.ict.equipments.check-out',
        'api.ict.equipments.update-end-use',
        'api.ict.equipments.report-location',
    ];

    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($this->app->environment('testing')) {
                return Limit::none();
            }

            $routeName = $request->route()?->getName() ?? 'api.unknown';
            $actorKey = (string) ($request->user()?->id ?: $request->ip());
            $isAuthenticated = $request->user() !== null;
            $isWriteRequest = !$request->isMethod('GET') && !$request->isMethod('HEAD') && !$request->isMethod('OPTIONS');
            $isSensitiveGuestRoute = !$isAuthenticated && in_array($routeName, self::SENSITIVE_GUEST_API_ROUTES, true);

            if ($isAuthenticated) {
                return [
                    Limit::perMinute($isWriteRequest ? 120 : 240)
                        ->by("api:user:minute:{$actorKey}"),
                    Limit::perHour($isWriteRequest ? 1800 : 7200)
                        ->by("api:user:hour:{$actorKey}"),
                ];
            }

            $limits = [
                Limit::perMinute($isWriteRequest ? 20 : 90)
                    ->by("api:guest:minute:{$actorKey}:{$routeName}"),
                Limit::perHour($isWriteRequest ? 240 : 1200)
                    ->by("api:guest:hour:{$actorKey}:{$routeName}"),
            ];

            if ($isSensitiveGuestRoute) {
                $limits[] = Limit::perMinute(10)
                    ->by("api:sensitive:minute:{$actorKey}:{$routeName}");
                $limits[] = Limit::perHour(60)
                    ->by("api:sensitive:hour:{$actorKey}:{$routeName}");
            }

            return $limits;
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
