<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Learning Analytics Tahap 3 (ADDITIVE):
 *  - la_page_views: jejak kunjungan halaman siswa (hanya nama rute + waktu; tanpa URL/IP/isi), dipangkas otomatis
 *  - la_daily_student.page_views: jumlah kunjungan per hari
 *  - la_account_merges: riwayat penggabungan akun (untuk audit dan pembatalan)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('la_page_views', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('route', 80);
            $t->timestamp('created_at')->useCurrent();
            $t->index(['user_id', 'created_at']);
            $t->index('created_at');
        });
        Schema::table('la_daily_student', function (Blueprint $t) {
            $t->unsignedSmallInteger('page_views')->default(0)->after('test_events');
        });
        Schema::create('la_account_merges', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('source_id');
            $t->unsignedBigInteger('target_id');
            $t->longText('moved');                    // {tabel: [id,...]} baris yang dipindahkan (untuk pembatalan)
            $t->longText('counts');                   // {tabel: n}
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('undone_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index(['source_id', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('la_account_merges');
        Schema::table('la_daily_student', fn (Blueprint $t) => $t->dropColumn('page_views'));
        Schema::dropIfExists('la_page_views');
    }
};
