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
    Schema::table('listening_materials', function (Blueprint $table) {

        $table->longText('passage')
            ->after('title');

    });
}

public function down(): void
{
    Schema::table('listening_materials', function (Blueprint $table) {

        $table->dropColumn('passage');

    });
}
};
