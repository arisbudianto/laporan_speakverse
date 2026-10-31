<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'school')) {
                $table->string('school')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'major')) {
                $table->string('major')->nullable()->after('school');
            }
            if (! Schema::hasColumn('users', 'grade')) {
                $table->string('grade', 10)->nullable()->after('major');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['school', 'major', 'grade']);
        });
    }
};
