<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entri_portofolio', function (Blueprint $t) {
            $t->enum('status_verifikasi', ['menunggu', 'diterima', 'ditolak'])->default('menunggu')->after('status')->index();
            $t->text('catatan_verifikasi')->nullable()->after('status_verifikasi');
            $t->timestamp('diverifikasi_pada')->nullable()->after('catatan_verifikasi');
        });

        DB::table('entri_portofolio')->where('status', 'terbit')->update(['status_verifikasi' => 'diterima', 'diverifikasi_pada' => now()]);
    }

    public function down(): void
    {
        Schema::table('entri_portofolio', fn (Blueprint $t) => $t->dropColumn(['status_verifikasi', 'catatan_verifikasi', 'diverifikasi_pada']));
    }
};