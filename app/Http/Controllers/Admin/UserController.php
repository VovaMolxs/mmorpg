<?php

namespace App\Http\Controllers\Admin;

use App\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('account_status', $request->get('status'));
        }

        $users = $query->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'statuses' => AccountStatus::cases(),
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Update the account status.
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'account_status' => ['required', 'string', 'in:active,inactive,banned,unverified'],
        ]);

        $user->update([
            'account_status' => AccountStatus::from($request->get('account_status')),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Статус аккаунта обновлен.');
    }

    /**
     * Ban a user.
     */
    public function ban(User $user): RedirectResponse
    {
        $user->update([
            'account_status' => AccountStatus::Banned,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Пользователь заблокирован.');
    }

    /**
     * Unban a user.
     */
    public function unban(User $user): RedirectResponse
    {
        $user->update([
            'account_status' => AccountStatus::Active,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Пользователь разблокирован.');
    }

    /**
     * Update max characters limit.
     */
    public function updateMaxCharacters(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'max_characters' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $user->update([
            'max_characters' => $request->get('max_characters'),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Лимит персонажей обновлен.');
    }
}
