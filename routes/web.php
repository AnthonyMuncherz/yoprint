<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

// Dashboard route
Route::get('/', [FileUploadController::class, 'index'])->name('dashboard');

// API routes for file upload
Route::post('/api/upload', [FileUploadController::class, 'upload'])->name('upload');
Route::get('/api/status', [FileUploadController::class, 'status'])->name('status');
Route::get('/api/uploads', [FileUploadController::class, 'uploads'])->name('uploads');
