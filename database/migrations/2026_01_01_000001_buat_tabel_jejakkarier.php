<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->enum('peran', ['pengguna', 'mentor', 'admin'])->default('pengguna')->after('password')->index();
            $t->boolean('aktif')->default(true)->after('peran')->index();
        });

        Schema::create('bidang_karier', function (Blueprint $t) {
            $t->id(); $t->string('nama', 80)->unique(); $t->timestamps();
        });

        Schema::create('keterampilan', function (Blueprint $t) {
            $t->id(); $t->string('nama', 80)->unique(); $t->timestamps();
        });

        Schema::create('profil_pengguna', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('pendidikan', 120)->nullable();
            $t->string('minat', 255)->nullable();
            $t->string('kota', 80)->nullable();
            $t->string('tujuan_karier', 160)->nullable();
            $t->text('bio')->nullable();
            $t->string('foto')->nullable();
            $t->timestamps();
        });

        Schema::create('profil_mentor', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->foreignId('bidang_karier_id')->nullable()->constrained('bidang_karier')->nullOnDelete();
            $t->string('jabatan', 120)->nullable();
            $t->text('pengalaman_kerja')->nullable();
            $t->unsignedTinyInteger('tahun_pengalaman')->default(0);
            $t->text('topik_mentoring')->nullable();
            $t->string('foto')->nullable();
            $t->enum('status_verifikasi', ['menunggu', 'terverifikasi', 'ditolak'])->default('menunggu')->index();
            $t->text('catatan_verifikasi')->nullable();
            $t->timestamp('diverifikasi_pada')->nullable();
            $t->timestamps();
        });

        Schema::create('users_keterampilan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('users_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('keterampilan_id')->constrained('keterampilan')->cascadeOnDelete();
            $t->enum('level', ['pemula', 'menengah', 'mahir', 'ahli'])->default('menengah');
            $t->unique(['users_id', 'keterampilan_id']);
        });

        Schema::create('entri_portofolio', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('judul', 160);
            $t->enum('jenis', ['proyek', 'pengalaman', 'sertifikat', 'studi_kasus', 'lainnya'])->default('proyek');
            $t->string('tautan')->nullable();
            $t->date('tanggal_mulai')->nullable();
            $t->date('tanggal_selesai')->nullable();
            $t->text('deskripsi')->nullable();
            $t->string('gambar')->nullable();
            $t->enum('status', ['terbit', 'draf'])->default('draf');
            $t->timestamp('terbit_pada')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'status']);
            $t->index('judul');
        });

        Schema::create('entri_keterampilan', function (Blueprint $t) {
            $t->foreignId('entri_portofolio_id')->constrained('entri_portofolio')->cascadeOnDelete();
            $t->foreignId('keterampilan_id')->constrained('keterampilan')->cascadeOnDelete();
            $t->primary(['entri_portofolio_id', 'keterampilan_id']);
        });

        Schema::create('slot_mentoring', function (Blueprint $t) {
            $t->id();
            $t->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $t->date('tanggal');
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->enum('status', ['tersedia', 'dipesan', 'ditutup'])->default('tersedia');
            $t->string('tautan_pertemuan')->nullable();
            $t->timestamps();
            $t->index(['mentor_id', 'tanggal']);
            $t->index(['status', 'tanggal']);
        });

        // slot_mentoring_id dibuat nullable+unique: saat sesi dibatalkan/ditolak, slot
        // dilepas (NULL) supaya slot bisa dipesan lagi, sedangkan riwayat tetap utuh
        // lewat kolom snapshot (mentor_id, tanggal, jam_*).
        Schema::create('sesi_mentoring', function (Blueprint $t) {
            $t->id();
            $t->foreignId('slot_mentoring_id')->nullable()->unique()->constrained('slot_mentoring')->nullOnDelete();
            $t->foreignId('pengguna_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $t->date('tanggal'); $t->time('jam_mulai'); $t->time('jam_selesai');
            $t->string('tujuan', 500);
            $t->enum('status', ['diajukan', 'dikonfirmasi', 'ditolak', 'dibatalkan', 'selesai'])->default('diajukan');
            $t->text('catatan')->nullable();
            $t->string('tautan_pertemuan')->nullable();
            $t->timestamps();
            $t->index(['pengguna_id', 'status']);
            $t->index(['mentor_id', 'status']);
        });

        Schema::create('rencana_karier', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('tujuan', 160);
            $t->date('target_tanggal')->nullable();
            $t->enum('status', ['aktif', 'tercapai'])->default('aktif');
            $t->timestamps();
        });

        Schema::create('langkah_karier', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rencana_karier_id')->constrained('rencana_karier')->cascadeOnDelete();
            $t->string('judul', 200);
            $t->date('target_tanggal')->nullable();
            $t->enum('status', ['belum', 'proses', 'selesai'])->default('belum');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['langkah_karier','rencana_karier','sesi_mentoring','slot_mentoring','entri_keterampilan','entri_portofolio','users_keterampilan','profil_mentor','profil_pengguna','keterampilan','bidang_karier'] as $n) Schema::dropIfExists($n);
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['peran', 'aktif']));
    }
};
