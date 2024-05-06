<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KelasMahasiswaController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::get('Absensi/Export', [AbsensiController::class, 'exportToExcel']);
Route::post('Absensi/Detect', [AbsensiController::class, 'detectMahasiswa']);

Route::middleware('auth:api')->group(function () {
});

Route::resource('Mahasiswa', MahasiswaController::class);
Route::resource('Absensi', AbsensiController::class);
Route::resource('Dosen', DosenController::class);
Route::resource('KelasMahasiswa', KelasMahasiswaController::class);
Route::resource('Kelas', KelasController::class);
// Route::get('Mahasiswa/{$id}', [KelasMahasiswaController::class, 'getMahasiswaByKelasId']);
Route::get('kelas-mahasiswa/{kelasId}/mahasiswa', [KelasMahasiswaController::class, 'getMahasiswaByKelasId']);
Route::get('kelas-mahasiswa/{kelasId}', [KelasMahasiswaController::class, 'getKelasMahasiswaByKelasId']);
Route::get('Absensi/{kelasId}/{mahasiswaId}', [AbsensiController::class, 'getAbsensibyId']);
