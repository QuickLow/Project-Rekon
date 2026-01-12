<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tidak membuat user default agar tidak ada kredensial bawaan.
        // Buat akun pertama (super admin) lewat halaman setup atau halaman Kelola User.
    }
}
