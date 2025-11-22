<?php

use App\Http\Controllers\Admin\CharacterController as AdminCharacterController;
use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemInstanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle.registrations')
        ->name('register');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Character routes
    Route::resource('characters', CharacterController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('characters/{character}/skills', [CharacterController::class, 'skills'])->name('characters.skills');
    Route::get('characters/{character}/inventory', [CharacterController::class, 'inventory'])->name('characters.inventory');

    // Item routes (API)
    Route::prefix('api')->name('api.')->group(function () {
        // Items (templates)
        Route::get('items', [ItemController::class, 'index'])->name('items.index');
        Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');

        // Item instances
        Route::get('item-instances/{itemInstance}', [ItemInstanceController::class, 'show'])->name('item-instances.show');

        // Item instances (character items)
        Route::prefix('characters/{character}')->group(function () {
            Route::get('inventory', [ItemInstanceController::class, 'inventory'])->name('characters.inventory');
            Route::get('equipment', [ItemInstanceController::class, 'equipment'])->name('characters.equipment');
            Route::post('items/equip', [ItemInstanceController::class, 'equip'])->name('characters.items.equip');
            Route::post('items/{itemInstance}/unequip', [ItemInstanceController::class, 'unequip'])->name('characters.items.unequip');
            Route::post('items/drop', [ItemInstanceController::class, 'drop'])->name('characters.items.drop');
            Route::post('items/move', [ItemInstanceController::class, 'move'])->name('characters.items.move');
            Route::post('items/use', [ItemInstanceController::class, 'use'])->name('characters.items.use');
        });
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->only(['index', 'show']);
    Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.update-status');
    Route::post('users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
    Route::patch('users/{user}/max-characters', [AdminUserController::class, 'updateMaxCharacters'])->name('users.update-max-characters');

    // Characters management
    Route::resource('characters', AdminCharacterController::class)->only(['index', 'show']);
    Route::post('characters/{character}/add-item', [AdminCharacterController::class, 'addItem'])->name('characters.add-item');
    Route::post('characters/{character}/update-skill', [AdminCharacterController::class, 'updateSkill'])->name('characters.update-skill');
    Route::post('characters/{character}/restore', [AdminCharacterController::class, 'restore'])->name('characters.restore');

    // Items management
    Route::resource('items', AdminItemController::class)->only(['index', 'create', 'store']);
});
