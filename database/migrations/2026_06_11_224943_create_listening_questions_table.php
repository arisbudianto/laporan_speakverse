<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('listening_questions', function (Blueprint $table) {

        $table->id();

        $table->foreignId('listening_material_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->text('question');

        $table->string('option_a');
        $table->string('option_b');
        $table->string('option_c');
        $table->string('option_d');
        $table->string('option_e');

        $table->string('correct_answer');

        $table->integer('score')
            ->default(10);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listening_questions');
    }
};
