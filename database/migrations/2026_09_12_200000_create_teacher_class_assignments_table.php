<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 30)->nullable()->after('email');
            }
        });

        Schema::create('teacher_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('school');
            $table->string('major');
            $table->string('grade', 10);
            $table->string('parallel', 5);
            $table->timestamps();

            $table->unique(
                ['user_id', 'school', 'major', 'grade', 'parallel'],
                'teacher_class_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_assignments');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nip')) {
                $table->dropColumn('nip');
            }
        });
    }
};
