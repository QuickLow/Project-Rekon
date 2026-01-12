<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function show()
    {
        if (User::query()->exists()) {
            abort(404);
        }

        return view('auth.setup');
    }

    public function store(Request $request)
    {
        if (User::query()->exists()) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'alpha_num', 'regex:/[A-Z]/'],
        ]);

        User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'super_admin',
        ]);

        return redirect()->route('login')->with('success', 'Super admin berhasil dibuat. Silakan login.');
    }
}
