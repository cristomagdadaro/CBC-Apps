<?php

use App\Http\Controllers\FormController;
use App\Models\Form;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::prefix('forms')->group(function () {
    Route::get('/{event?}', [FormController::class, 'formGuestView'])->name('forms.guest.index');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('apps')->group(function () {
        Route::prefix('forms')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Forms/FormIndex');
            })->name('forms.index');

            Route::get('/create', function () {
                return Inertia::render('Forms/FormCreate');
            })->name('forms.create');

            Route::get('/scan', function () {
                return Inertia::render('Forms/FormScan');
            })->name('forms.scan');

            Route::get('/update/{event_id?}', function ($event_id = null) {
                if (!$event_id) {
                    $event_id = request()->input('event_id'); // Fallback to query parameter
                }

                if (!$event_id) {
                    $event_id = request()->query('event_id');
                }

                return Inertia::render('Forms/FormUpdate', [
                    'data' => Form::where('event_id', $event_id)->with('participants:participants.id,name,email,phone,organization')->first(),
                ]);
            })->name('forms.update');
        });
    });

});
