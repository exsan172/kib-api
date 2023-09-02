<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\V1\Auth\LoginController;
use App\Http\Controllers\V1\Auth\RegisterController;
use App\Http\Controllers\V1\Master\KategoriBarangController;
use App\Http\Controllers\V1\Master\MetodePenyusutanController;
use App\Http\Controllers\V1\Sites\MenuController;
use App\Http\Controllers\V1\Sites\RoleController;
use App\Http\Controllers\V1\User\MenuUserController;
use App\Http\Controllers\V1\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        // Login Api
        Route::post('login', [LoginController::class, 'login']);

        // Register Api
        Route::post('register', [RegisterController::class, 'register']);

        // email verification
        Route::post('verification/resend-email', [RegisterController::class, 'resendEmailVerification'])->middleware('auth:sanctum');
        Route::get('verification/verify/{token}', [RegisterController::class, 'verificationEmail'])->middleware('auth:sanctum');

        // reset Password
        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
        Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
    });

    // sites
    Route::prefix('sites')->middleware('auth:sanctum')->group(function () {
        // sites/account
        Route::resource('menu', MenuController::class);
        Route::post('menu/list', [MenuController::class, 'list']);
        Route::post('menu/role/{id}', [MenuController::class, 'updateMenuRole']);

        // role
        Route::resource('role', RoleController::class);
        Route::post('role/list', [RoleController::class, 'list']);
    });

    // master
    Route::prefix('master')->middleware('auth:sanctum')->group(function () {
        // master/kategori/barang
        Route::resource('kategori/barang', KategoriBarangController::class);
        Route::post('kategori/barang/list', [KategoriBarangController::class, 'list']);
        // master/metode-penyusutan
        Route::resource('metode-penyusutan/barang', MetodePenyusutanController::class);
        Route::post('metode-penyusutan/barang/list', [MetodePenyusutanController::class, 'list']);
    });

    // master
    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('lokasi', LokasiController::class);
        Route::post('lokasi/list', [LokasiController::class, 'list']);

        Route::resource('barang', BarangController::class);
        Route::post('barang/list', [BarangController::class, 'list']);

        Route::resource('jadwal', JadwalPengecekanController::class);
        Route::post('jadwal/list', [JadwalPengecekanController::class, 'list']);
    });

    // user
    Route::prefix('user')->middleware('auth:sanctum')->group(function () {
        Route::get('menu', [MenuUserController::class, 'getMenuUser']);

        // contact
        Route::resource('contact', UserController::class);
        Route::post('contact/list', [UserController::class, 'list']);
    });
});
