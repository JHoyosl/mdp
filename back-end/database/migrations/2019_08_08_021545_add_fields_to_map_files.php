<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToMapFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('map_files', function (Blueprint $table) {
            
            $table->text('separator')->nullable;
            $table->text('extension');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('map_files', function (Blueprint $table) {
            $table->dropColumn('separator');
            $table->dropColumn('extension');
        });
    }
}
