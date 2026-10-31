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
        Schema::create('reading_materials', function (Blueprint $table) {

        $table->id();

        $table->foreignId('lesson_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('title');

        $table->longText('passage');

        $table->text('instruction')->nullable();

        $table->string('image')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_materials');
    }
};
