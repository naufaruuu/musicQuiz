<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('records', function (Blueprint $table) {
            $table->bigInteger('duration')->default(0)->after('score'); // Duration in milliseconds
        });
    }
    
    public function down()
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
    
};
