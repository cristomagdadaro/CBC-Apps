<?php

use App\Http\Controllers\GoLinkController;
use App\Models\GoLinkRedirect;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_GOLINK])
    ->get('/go/{slug}', [GoLinkController::class, 'redirect'])
    ->name('golinks.redirect');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_GOLINK])
            ->prefix('go-links')
            ->group(function () {
                Route::get('/', function () {
                    abort_unless(request()->user()?->can('golinks.manage') || request()->user()?->is_admin, 403);

                    return Inertia::render('GoLinks/GoLinks', [
                        'fromUrl' => route('dashboard'),
                        'publicBaseUrl' => rtrim((string) config('golink.public_base_url'), '/'),
                    ]);
                })->name('golinks.index');

                Route::get('/create', function () {
                    abort_unless(request()->user()?->can('golinks.manage') || request()->user()?->is_admin, 403);

                    return Inertia::render('GoLinks/components/CreateGoLinkForm', [
                        'fromUrl' => route('golinks.index'),
                        'publicBaseUrl' => rtrim((string) config('golink.public_base_url'), '/'),
                    ]);
                })->name('golinks.create');

                Route::get('/{id}', function (int $id) {
                    abort_unless(request()->user()?->can('golinks.manage') || request()->user()?->is_admin, 403);

                    $data = GoLinkRedirect::query()->findOrFail($id);

                    return Inertia::render('GoLinks/components/EditGoLinkForm', [
                        'data' => $data,
                        'fromUrl' => route('golinks.index'),
                        'publicBaseUrl' => rtrim((string) config('golink.public_base_url'), '/'),
                    ]);
                })->name('golinks.show');
            });
    });
});
