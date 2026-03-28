<?php

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::prefix('system')->group(function () {
            Route::middleware(['can:users.manage'])->prefix('users')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('System/Users/UsersIndex');
                })->name('system.users.index');

                Route::get('/create', function () {
                    return Inertia::render('System/Users/CreateUser', [
                        'roleOptions' => RoleEnum::values(),
                        'permissionOptions' => config('rbac.permissions', []),
                    ]);
                })->name('system.users.create');

                Route::get('/{id}', function () {
                    return Inertia::render('System/Users/EditUser', [
                        'data' => User::query()->with('roles:id,name,label')->findOrFail(request()->route('id')),
                        'roleOptions' => RoleEnum::values(),
                        'permissionOptions' => config('rbac.permissions', []),
                    ]);
                })->name('system.users.show');
            });
        });
    });
});