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
        Schema::create('units', function (Blueprint $table) {

        $table->id();

        $table->string('title');
        $table->string('subtitle')->nullable();

        $table->text('description')->nullable();

        $table->integer('order_number')->index();

        $table->enum('type', [
            'pretest',
            'unit',
            'posttest'
        ]);

        $table->enum('status', [
            'active',
            'inactive'
        ])->default('active');

        $table->timestamps();

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
