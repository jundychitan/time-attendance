<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'company', 'is_super_admin', 'created_at']);

        return Inertia::render('admin-users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin-users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company' => ['nullable', 'string', 'max:255'],
            'is_super_admin' => ['sometimes', 'boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        User::query()->create($validated);

        return redirect('/admin-users')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin-users/Edit', [
            'adminUser' => $user->only(['id', 'name', 'email', 'company', 'is_super_admin']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'company' => ['nullable', 'string', 'max:255'],
            'is_super_admin' => ['sometimes', 'boolean'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect('/admin-users')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect('/admin-users')
            ->with('success', 'Admin user deleted successfully.');
    }
}
