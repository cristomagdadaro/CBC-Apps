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

Route::prefix('guest')->group(function () {
    Route::get('/forms/{event?}', [FormController::class, 'formGuestView'])->name('forms.guest.index');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('forms')->group(function () {
        Route::get('/', function () {
            $temp = (new App\Models\Form)->newQuery();
            return Inertia::render('Forms/FormIndex', [
                'listOfForms' => $temp->withCount('participants')->get(),
                'rawSql' => $temp->with('participants')->toRawSql()
            ]);
        })->name('forms.index');
    });
});
