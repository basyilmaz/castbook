<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = in_array($data['is_active'], [true, 1, '1'], true);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $isActive,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Kullanıcı başarıyla oluşturuldu.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = in_array($data['is_active'], [true, 1, '1'], true);

        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()
                ->withErrors(['role' => 'Kendi hesabınızın rolünü değiştiremezsiniz.'])
                ->withInput();
        }

        $isDemotingAdmin = $user->role === 'admin' && ($data['role'] !== 'admin' || ! $isActive);
        if ($isDemotingAdmin) {
            $otherAdmins = User::query()
                ->where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                return back()
                    ->withErrors(['role' => 'Sistemde en az bir yönetici kalmalı.'])
                    ->withInput();
            }
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $isActive,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        if ($user->id === $request->user()->id && ! $isActive) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Hesabınız pasif hale getirildi.']);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'Kullanıcı güncellendi.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Kendi hesabınızı silemezsiniz.']);
        }

        if ($user->role === 'admin') {
            $otherAdmins = User::query()
                ->where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                return back()->withErrors(['user' => 'Son yöneticiyi silemezsiniz.']);
            }
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'Kullanıcı silindi.');
    }
}
