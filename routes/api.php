<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\JadwalPengecekanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\V1\Auth\LoginController;
use App\Http\Controllers\V1\Auth\RegisterController;
use App\Http\Controllers\V1\Master\KategoriBarangController;
use App\Http\Controllers\V1\Master\MetodePenyusutanController;
use App\Http\Controllers\V1\Pemindahan\HibahBarangController;
use App\Http\Controllers\V1\Pemindahan\JualBarangController;
use App\Http\Controllers\V1\Pemindahan\PemusnahanBarangController;
use App\Http\Controllers\V1\Pemindahan\TukarBarangController;
use App\Http\Controllers\V1\Sites\MenuController;
use App\Http\Controllers\V1\Sites\RoleController;
use App\Http\Controllers\V1\User\MenuUserController;
use App\Http\Controllers\V1\User\UserController;
use App\Http\Controllers\DashboardController;
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
        Route::delete('lokasi/{id}', [LokasiController::class, 'destroy']);

        Route::post('barang/list', [BarangController::class, 'list']);
        Route::post('barang/import', [BarangController::class, 'importDataBarang']);
        Route::post('barang/print', [BarangController::class, 'printDataBarang']);
        Route::get('barang/export', [BarangController::class, 'exportDataBarang']);
        Route::delete('barang/delete-foto/{id}', [BarangController::class, 'deleteFotoBarang']);
        Route::get('barang/barcode', [BarangController::class, 'barcode']);
        Route::get('barang/barcode/{barcode}', [BarangController::class, 'barangByBarcode']);
        Route::get('barang/history-update/{id}', [BarangController::class, 'historyUpdateBarang']);
        Route::resource('barang', BarangController::class);

        Route::resource('jadwal', JadwalPengecekanController::class);
        Route::post('jadwal/list', [JadwalPengecekanController::class, 'list']);

        // pemindahtanganan
        Route::prefix('pemindahan')->group(function () {
            // jual barang
            Route::resource('jual', JualBarangController::class);
            Route::post('jual/list', [JualBarangController::class, 'list']);

            // tukar barang
            Route::resource('tukar', TukarBarangController::class);
            Route::post('tukar/list', [TukarBarangController::class, 'list']);

            // hibah barang
            Route::resource('hibah', HibahBarangController::class);
            Route::post('hibah/list', [HibahBarangController::class, 'list']);

            // pemusnahan barang
            Route::resource('pemusnahan', PemusnahanBarangController::class);
            Route::post('pemusnahan/list', [PemusnahanBarangController::class, 'list']);
        });
    });

    // user
    Route::prefix('user')->middleware('auth:sanctum')->group(function () {
        Route::get('menu', [MenuUserController::class, 'getMenuUser']);

        // contact
        Route::resource('contact', UserController::class);
        Route::post('contact/list', [UserController::class, 'list']);
    });

    Route::get('dashboard', [UserProfileController::class, 'dashboard'])->middleware('auth:sanctum');
    Route::get('dashboard/data', [DashboardController::class, 'dashboardData'])->middleware('auth:sanctum');
    Route::prefix('account')->middleware('auth:sanctum')->group(function () {
        // profile
        Route::get('user/profile', [UserProfileController::class, 'getUserProfile']);
        Route::post('user/profile', [UserProfileController::class, 'updateProfile']);
        Route::post('user/password', [UserProfileController::class, 'updatePassword']);
    });
});
