<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sebelumnya role default adalah 'user' (punya akses upload).
        // Sekarang kita pakai 3 role: super_admin, admin, user(read-only).
        // Migrasikan role lama 'user' menjadi 'admin' agar akses lama tidak berubah.
        DB::table('users')->where('role', 'user')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        // Rollback: kembalikan admin menjadi user (sesuai skema lama).
        DB::table('users')->where('role', 'admin')->update(['role' => 'user']);
    }
};
