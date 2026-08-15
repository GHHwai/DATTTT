<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('role')
            ->when($request->query('role'), function ($query, $roleName) {
                $query->whereHas('role', fn ($q) => $q->where('name', $roleName));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('label')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'roleFilter' => $request->query('role'),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => Role::orderBy('label')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Prevent an admin from accidentally demoting themselves and
        // getting locked out of user management.
        if ($user->id === $request->user()->id
            && $user->role?->name === 'admin'
            && (int) $validated['role_id'] !== $user->role_id) {
            return back()->withErrors(['role_id' => 'You cannot change your own admin role.'])->withInput();
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => filled($validated['password'] ?? null)
                ? Hash::make($validated['password'])
                : $user->password,
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Account deleted.');
    }
}
