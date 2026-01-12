<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        return view('users', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'alpha_num', 'regex:/[A-Z]/', 'confirmed'],
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
        ]);

        User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return back()->with('success', 'User berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $current = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
            'password' => ['nullable', 'string', 'min:8', 'alpha_num', 'regex:/[A-Z]/', 'confirmed'],
        ]);

        // Jangan izinkan mengubah role diri sendiri (biar tidak terkunci / salah set role)
        if ($current && $current->id === $user->id && $data['role'] !== $user->role) {
            return back()->with('error', 'Tidak bisa mengubah role akun yang sedang digunakan.');
        }

        // Jangan izinkan menurunkan role super admin terakhir
        if ($user->role === 'super_admin' && $data['role'] !== 'super_admin') {
            $superAdminCount = User::query()->where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Tidak bisa mengubah role super admin terakhir.');
            }
        }

        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $current = $request->user();

        if ($current && $current->id === $user->id) {
            return back()->with('error', 'Tidak bisa menghapus akun yang sedang digunakan.');
        }

        if ($user->role === 'super_admin') {
            $superAdminCount = User::query()->where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Tidak bisa menghapus super admin terakhir.');
            }
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
