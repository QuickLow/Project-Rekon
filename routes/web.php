<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekonController;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

Route::get('/', [RekonController::class, 'index'])->name('dashboard');
Route::get('/upload', [RekonController::class, 'create'])->name('upload');
Route::post('/upload', [RekonController::class, 'store'])->name('upload.store');
// Hierarki rekonsiliasi baru
Route::get('/rekon/{id}', [RekonController::class, 'mitraList'])->name('rekon');
Route::get('/rekon/{id}/mitra/{mitra}', [RekonController::class, 'lopList'])->name('rekon.mitra');
Route::get('/rekon/{id}/mitra/{mitra}/lop/{lop}', [RekonController::class, 'detail'])->name('rekon.detail');



