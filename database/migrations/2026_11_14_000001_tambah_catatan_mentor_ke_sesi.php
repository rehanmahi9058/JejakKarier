<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_mentoring', function (Blueprint $t) {
            $t->json('checklist')->nullable()->after('tautan_pertemuan');
            $t->text('umpan_balik')->nullable()->after('checklist');
            $t->json('action_items')->nullable()->after('umpan_balik');
        });
    }

    public function down(): void
    {
        Schema::table('sesi_mentoring', fn (Blueprint $t) => $t->dropColumn(['checklist', 'umpan_balik', 'action_items']));
    }
};
