<?php
use App\Http\Controllers\{AuthController, DashboardController, KatalogController, MentoringController, PetaKarierController, SesiController};
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'beranda'])->name('beranda');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
Route::get('/katalog/{mentor}', [KatalogController::class, 'detail'])->name('katalog.detail');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'formDaftar'])->name('register');
    Route::post('/register', [AuthController::class, 'daftar']);
    Route::get('/login', [AuthController::class, 'formMasuk'])->name('login');
    Route::post('/login', [AuthController::class, 'masuk']);
});
Route::post('/logout', [AuthController::class, 'keluar'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mentoring', [MentoringController::class, 'index'])->name('mentoring.index');

    Route::get('/profil', [\App\Http\Controllers\ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [\App\Http\Controllers\ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [\App\Http\Controllers\ProfilController::class, 'ubahPassword'])->name('profil.password');

    // TODO tahap berikutnya: /mentor/* (portofolio, slot), /sesi

    Route::prefix('peta-karier')->name('peta-karier.')->group(function () {
        Route::get('/', [PetaKarierController::class, 'index'])->name('index');
        Route::post('/tujuan', [PetaKarierController::class, 'simpanTujuan'])->name('tujuan');
        Route::post('/langkah', [PetaKarierController::class, 'tambahLangkah'])->name('langkah.tambah');
        Route::patch('/langkah/{langkah}', [PetaKarierController::class, 'ubahLangkah'])->name('langkah.ubah');
        Route::delete('/langkah/{langkah}', [PetaKarierController::class, 'hapusLangkah'])->name('langkah.hapus');
    });

    Route::post('/slot/{slot}/pesan', [SesiController::class, 'pesan'])->name('sesi.pesan');
    Route::post('/sesi/{sesi}/{aksi}', [SesiController::class, 'ubahStatus'])->name('sesi.aksi');

    // TODO tahap berikutnya: /profil, /mentor/* (portofolio, slot, profil), /sesi
    Route::prefix('mentor')->middleware('mentor')->group(function () {});
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', fn () => view('dashboard.index'))->name('admin.ringkasan'); // TODO: AdminController
});

