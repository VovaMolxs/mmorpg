<?php

use App\Http\Controllers\Admin\CharacterController as AdminCharacterController;
use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Admin\LocationItemSpawnController;
use App\Http\Controllers\Admin\NpcController as AdminNpcController;
use App\Http\Controllers\Admin\NpcSpawnController as AdminNpcSpawnController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WorldMapController;
use App\Http\Controllers\Api\GameSessionController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemInstanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
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

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')
            ->with('success', 'Email успешно подтвержден!');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/game', [GameController::class, 'index'])->name('game');

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
            Route::post('items/pick-up', [ItemInstanceController::class, 'pickUp'])->name('characters.items.pick-up');
            Route::post('items/move', [ItemInstanceController::class, 'move'])->name('characters.items.move');
            Route::post('items/use', [ItemInstanceController::class, 'use'])->name('characters.items.use');
        });

        // Location routes (API)
        Route::get('location/current', [LocationController::class, 'current'])->name('location.current');
        Route::post('location/move', [LocationController::class, 'move'])->name('location.move');
        Route::get('location/{location}/exits', [LocationController::class, 'exits'])->name('location.exits');
        Route::get('location/{location}/items', [LocationController::class, 'items'])->name('location.items');
        Route::get('location/{location}/players', [LocationController::class, 'players'])->name('location.players');
        Route::get('world/map', [LocationController::class, 'map'])->name('world.map');

        // Game session routes (API)
        Route::prefix('game')->name('game.')->group(function () {
            Route::post('enter-world', [GameSessionController::class, 'enterWorld'])->name('enter-world');
            Route::post('leave-world', [GameSessionController::class, 'leaveWorld'])->name('leave-world');
            Route::post('keep-alive', [GameSessionController::class, 'keepAlive'])->name('keep-alive');
            Route::get('session-status', [GameSessionController::class, 'sessionStatus'])->name('session-status');
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
    Route::get('characters/online', [AdminCharacterController::class, 'online'])->name('characters.online');
    Route::resource('characters', AdminCharacterController::class)->only(['index', 'show']);
    Route::post('characters/{character}/add-item', [AdminCharacterController::class, 'addItem'])->name('characters.add-item');
    Route::post('characters/{character}/update-skill', [AdminCharacterController::class, 'updateSkill'])->name('characters.update-skill');
    Route::post('characters/{character}/restore', [AdminCharacterController::class, 'restore'])->name('characters.restore');
    Route::post('characters/{character}/move', [AdminCharacterController::class, 'move'])->name('characters.move');

    // Items management
    Route::resource('items', AdminItemController::class)->except(['show']);

    // World map management
    Route::get('world-map', [WorldMapController::class, 'index'])->name('world-map.index');
    Route::post('world-map', [WorldMapController::class, 'store'])->name('world-map.store');
    Route::get('world-map/{location}', [WorldMapController::class, 'show'])->name('world-map.show');
    Route::put('world-map/{location}', [WorldMapController::class, 'update'])->name('world-map.update');
    Route::post('world-map/{location}/exits', [WorldMapController::class, 'updateExits'])->name('world-map.update-exits');
    Route::delete('world-map/{location}', [WorldMapController::class, 'destroy'])->name('world-map.destroy');

    // Item spawns management
    Route::resource('item-spawns', LocationItemSpawnController::class)->except(['show']);

    // NPCs management
    Route::resource('npcs', AdminNpcController::class)->except(['show']);

    // NPC spawns management
    Route::resource('npc-spawns', AdminNpcSpawnController::class)->except(['show']);
});
