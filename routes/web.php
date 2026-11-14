<?php
use App\Http\Controllers\{AdminController, AuthController, DashboardController, KatalogController, MentoringController, PetaKarierController, SesiController, SlotMentorController, PortofolioController, SesiMentorController};
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
    Route::post('/sesi/{sesi}/catatan', [SesiMentorController::class, 'simpanCatatan'])->middleware('mentor')->name('sesi.catatan');
    Route::post('/sesi/{sesi}/{aksi}', [SesiController::class, 'ubahStatus'])->name('sesi.aksi');
    Route::get('/sesi', [SesiMentorController::class, 'index'])->middleware('mentor')->name('sesi.index');

    Route::prefix('mentor')->name('mentor.')->middleware('mentor')->group(function () {
        Route::get('/slot', [SlotMentorController::class, 'index'])->name('slot.index');
        Route::post('/slot', [SlotMentorController::class, 'store'])->name('slot.store');
        Route::post('/slot/tutup-massal', [SlotMentorController::class, 'tutupMassal'])->name('slot.tutup-massal');
        Route::patch('/slot/{slot}/tutup', [SlotMentorController::class, 'tutup'])->name('slot.tutup');
        Route::patch('/slot/{slot}/buka', [SlotMentorController::class, 'buka'])->name('slot.buka');
        Route::delete('/slot/{slot}', [SlotMentorController::class, 'hapus'])->name('slot.hapus');

        Route::get('/portofolio', [PortofolioController::class, 'index'])->name('portofolio.index');
        Route::get('/portofolio/buat', [PortofolioController::class, 'create'])->name('portofolio.create');
        Route::post('/portofolio', [PortofolioController::class, 'store'])->name('portofolio.store');
        Route::get('/portofolio/{portofolio}', [PortofolioController::class, 'show'])->name('portofolio.show');
        Route::get('/portofolio/{portofolio}/ubah', [PortofolioController::class, 'edit'])->name('portofolio.edit');
        Route::put('/portofolio/{portofolio}', [PortofolioController::class, 'update'])->name('portofolio.update');
        Route::delete('/portofolio/{portofolio}', [PortofolioController::class, 'destroy'])->name('portofolio.destroy');
    });
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'ringkasan'])->name('admin.ringkasan');
    Route::get('/profil', [AdminController::class, 'profil'])->name('admin.profil');
    Route::put('/profil', [AdminController::class, 'ubahProfil'])->name('admin.profil.ubah');
    Route::get('/akun', [AdminController::class, 'akun'])->name('admin.akun');
    Route::get('/portofolio', [AdminController::class, 'portofolio'])->name('admin.portofolio');
    Route::post('/portofolio/{portofolio}/tinjau', [AdminController::class, 'tinjauPortofolio'])->name('admin.portofolio.tinjau');
    Route::post('/mentor/{profil}/verifikasi', [AdminController::class, 'verifikasi'])->name('admin.mentor.verifikasi');
    Route::patch('/akun/{user}/status', [AdminController::class, 'ubahStatus'])->name('admin.akun.status');
});