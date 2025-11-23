<?php

namespace App\Http\Controllers\Auth;

use App\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $user = DB::transaction(function () use ($validated, $request) {
                $user = User::create([
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'account_status' => AccountStatus::Unverified,
                    'max_characters' => 3,
                    'preferred_language' => $validated['preferred_language'] ?? 'en',
                    'game_settings' => User::getDefaultGameSettings(),
                    'time_played_total' => 0,
                ]);

                DB::table('registration_attempts')->insert([
                    'ip_address' => $request->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $user;
            });

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Регистрация успешна! Добро пожаловать в игру.');
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.']);
        }
    }
}
