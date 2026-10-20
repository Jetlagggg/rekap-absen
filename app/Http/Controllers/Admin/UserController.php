<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower((string) $request->input('email')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(User::roles())],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower((string) $request->input('email')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(User::roles())],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $this->ensureSafeRoleChange($user, $data['role'], $data['is_active'], $request->user());

        $user->update($data);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => null,
        ])->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Kata sandi berhasil diperbarui. Sampaikan kata sandi baru kepada pengguna melalui kanal aman.');
    }

    private function ensureSafeRoleChange(User $user, string $role, bool $isActive, User $actor): void
    {
        if ($user->is($actor) && ($role !== User::ROLE_ADMIN || ! $isActive)) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat menurunkan role atau menonaktifkan akun sendiri.',
            ]);
        }

        if (! $user->isAdmin() || ($role === User::ROLE_ADMIN && $isActive)) {
            return;
        }

        $hasOtherAdmin = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->whereKeyNot($user->getKey())
            ->exists();

        if (! $hasOtherAdmin) {
            throw ValidationException::withMessages([
                'role' => 'Minimal satu administrator aktif harus tersedia.',
            ]);
        }
    }
}
