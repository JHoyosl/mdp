<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToExternalTxTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('external_tx_types', function (Blueprint $table) {
            //
            $table->string('type')->default('SIMPLE')->after('reference');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('external_tx_types', function (Blueprint $table) {
            //
            $table->dropColumn('type');
        });
    }
}
