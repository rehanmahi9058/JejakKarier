<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entri_portofolio', function (Blueprint $table) {
            // String dipakai agar status menunggu dan ditolak dapat ditambahkan tanpa
            // bergantung pada perubahan ENUM yang berbeda antarversi MySQL/MariaDB.
            $table->string('status', 20)->default('draf')->change();
            $table->text('catatan_admin')->nullable()->after('terbit_pada');
            $table->timestamp('ditinjau_pada')->nullable()->after('catatan_admin');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('entri_portofolio', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['catatan_admin', 'ditinjau_pada']);
            $table->enum('status', ['terbit', 'draf'])->default('draf')->change();
        });
    }
};
