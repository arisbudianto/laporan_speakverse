<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Analitik Lanjutan Tahap 2 (ADDITIVE; down() mengembalikan):
 *  - users: is_test, exclude_from_analytics, exclude_reason          (kohort penelitian)
 *  - la_daily_student: learn_events, test_events                      (aktivitas belajar vs tes)
 *  - la_bkt_params: reliable, at_bound, ll_baseline; la_mastery: reliable   (diagnostik BKT)
 *  - la_form_stats: n_flagged, n_flagged_severe, alpha_without_flagged      (kualitas soal)
 *  - la_item_reviews                                                  (antrean tinjauan butir)
 *  - la_consents: source, batch_id; la_consent_batches                (persetujuan kolektif sekolah/orang tua)
 *  - la_interventions: assignment, recheck_at                         (pengacakan & jadwal tindak lanjut ulang)
 *  - level skor risiko dilebarkan agar memuat 'belum_mulai'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_test')->default(false)->after('research_group');
            $t->boolean('exclude_from_analytics')->default(false)->after('is_test');
            $t->string('exclude_reason', 120)->nullable()->after('exclude_from_analytics');
            $t->index(['is_test', 'exclude_from_analytics'], 'users_la_cohort_idx');
        });
        Schema::table('la_daily_student', function (Blueprint $t) {
            $t->unsignedInteger('learn_events')->default(0)->after('events');
            $t->unsignedInteger('test_events')->default(0)->after('learn_events');
        });
        Schema::table('la_bkt_params', function (Blueprint $t) {
            $t->boolean('reliable')->default(false)->after('is_default');
            $t->string('at_bound', 40)->nullable()->after('reliable');
            $t->decimal('ll_baseline', 12, 2)->nullable()->after('log_likelihood');
        });
        Schema::table('la_mastery', function (Blueprint $t) {
            $t->boolean('reliable')->default(false)->after('wheel_spinning');
        });
        Schema::table('la_form_stats', function (Blueprint $t) {
            $t->unsignedSmallInteger('n_flagged')->nullable();
            $t->unsignedSmallInteger('n_flagged_severe')->nullable();
            $t->decimal('alpha_without_flagged', 6, 3)->nullable();
        });
        Schema::create('la_item_reviews', function (Blueprint $t) {
            $t->id();
            $t->string('form', 120);
            $t->string('item', 60);
            $t->string('status', 20)->default('belum');
            $t->text('note')->nullable();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('updated_at')->nullable();
            $t->unique(['form', 'item']);
        });
        Schema::create('la_consent_batches', function (Blueprint $t) {
            $t->id();
            $t->string('school', 150);
            $t->string('grade', 10)->nullable();
            $t->string('parallel', 10)->nullable();
            $t->string('basis', 20);                 // sekolah | orangtua
            $t->string('decision', 10);              // setuju | tolak
            $t->string('document_no', 120)->nullable();
            $t->text('note')->nullable();
            $t->unsignedInteger('affected')->default(0);
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::table('la_consents', function (Blueprint $t) {
            $t->string('source', 20)->default('siswa')->after('accepted');   // siswa | sekolah | orangtua
            $t->unsignedBigInteger('batch_id')->nullable()->after('source');
        });
        Schema::table('la_interventions', function (Blueprint $t) {
            $t->string('assignment', 20)->nullable()->after('action');       // langsung | tunda (acak)
            $t->timestamp('recheck_at')->nullable()->after('followed_at');
        });
        foreach (['la_risk_scores' => 'level', 'la_risk_history' => 'level', 'la_interventions' => 'risk_level'] as $tbl => $col) {
            DB::statement("ALTER TABLE `$tbl` MODIFY `$col` VARCHAR(20) " . ($col === 'risk_level' ? 'NULL' : 'NOT NULL'));
        }
    }

    public function down(): void
    {
        foreach (['la_risk_scores' => 'level', 'la_risk_history' => 'level', 'la_interventions' => 'risk_level'] as $tbl => $col) {
            DB::statement("UPDATE `$tbl` SET `$col` = 'rendah' WHERE `$col` = 'belum_mulai'");
            DB::statement("ALTER TABLE `$tbl` MODIFY `$col` VARCHAR(10) " . ($col === 'risk_level' ? 'NULL' : 'NOT NULL'));
        }
        Schema::table('la_interventions', fn (Blueprint $t) => $t->dropColumn(['assignment', 'recheck_at']));
        Schema::table('la_consents', fn (Blueprint $t) => $t->dropColumn(['source', 'batch_id']));
        Schema::dropIfExists('la_consent_batches');
        Schema::dropIfExists('la_item_reviews');
        Schema::table('la_form_stats', fn (Blueprint $t) => $t->dropColumn(['n_flagged', 'n_flagged_severe', 'alpha_without_flagged']));
        Schema::table('la_mastery', fn (Blueprint $t) => $t->dropColumn('reliable'));
        Schema::table('la_bkt_params', fn (Blueprint $t) => $t->dropColumn(['reliable', 'at_bound', 'll_baseline']));
        Schema::table('la_daily_student', fn (Blueprint $t) => $t->dropColumn(['learn_events', 'test_events']));
        Schema::table('users', function (Blueprint $t) {
            $t->dropIndex('users_la_cohort_idx');
            $t->dropColumn(['is_test', 'exclude_from_analytics', 'exclude_reason']);
        });
    }
};
