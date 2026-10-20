<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    // 1. DASHBOARD
    Route::get('/', [AttendanceController::class, 'index'])->name('dashboard');

    // 2. INPUT ABSEN
    Route::get('/input-absen', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/simpan-massal', [AttendanceController::class, 'storeMassal'])->name('attendance.storeMassal');

    // 3. LAPORAN WEB
    Route::get('/laporan-tpp', [AttendanceController::class, 'rekapBulanan'])->name('attendance.rekap');

    // 4. DOWNLOAD PDF
    Route::get('/download-laporan-pdf', [AttendanceController::class, 'downloadPdf'])->name('attendance.downloadPdf');

    // 5. PEGAWAI
    Route::get('/tambah-pegawai', [AttendanceController::class, 'createEmployee'])->name('employee.create');
    Route::post('/simpan-pegawai', [AttendanceController::class, 'storeEmployee'])->name('employee.store');
    Route::delete('/hapus-pegawai/{id}', [AttendanceController::class, 'destroyEmployee'])->name('employee.destroy');
    Route::put('/employee/{id}', [AttendanceController::class, 'updateEmployee'])->name('employee.update');
    Route::post('/import-pegawai', [AttendanceController::class, 'importEmployee'])->name('pegawai.import');

    // 6. ADMIN: PENGGUNA DAN ROLE
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/password', [AdminUserController::class, 'updatePassword'])->name('users.password');
    });

    // 7. LOGOUT
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
