<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('listening_questions', function (Blueprint $table) {
            $table->text('instruction')->nullable()->after('listening_material_id');
        });
    }

    public function down()
    {
        Schema::table('listening_questions', function (Blueprint $table) {
            $table->dropColumn('instruction');
        });
    }
};
