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
    Schema::create('listening_materials', function (Blueprint $table) {

        $table->id();

        $table->foreignId('lesson_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('title');

        $table->longText('script_text');

        $table->string('audio_file')->nullable();

        $table->text('instruction')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listening_materials');
    }
};
