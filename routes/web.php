<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekonController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\AdminUserController;

Route::get('/', function () {
	return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('login.post');

	// Setup hanya muncul jika belum ada user.
	Route::get('/setup', [SetupController::class, 'show'])->name('setup');
	Route::post('/setup', [SetupController::class, 'store'])->name('setup.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
	Route::get('/dashboard', [RekonController::class, 'index'])->name('dashboard');

	// Upload data (admin & super admin)
	Route::middleware('role:admin,super_admin')->group(function () {
		Route::get('/upload', [RekonController::class, 'create'])->name('upload');
		Route::get('/upload/{id}/edit', [RekonController::class, 'editUpload'])->name('upload.edit');
		Route::post('/upload', [RekonController::class, 'store'])->name('upload.store');
	});

	// Hierarki rekonsiliasi baru (user boleh lihat)
	Route::get('/rekon/{id}', [RekonController::class, 'mitraList'])->name('rekon');
	Route::get('/rekon/{id}/mitra/{mitra}', [RekonController::class, 'lopList'])->name('rekon.mitra');
	Route::get('/rekon/{id}/mitra/{mitra}/lop/{lop}', [RekonController::class, 'detail'])->name('rekon.detail');

	// Aksi khusus super admin
	Route::middleware('superadmin')->group(function () {
		Route::delete('/project/{id}', [RekonController::class, 'destroyProject'])->name('project.destroy');

		Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
		Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
		Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
		Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
	});
});



