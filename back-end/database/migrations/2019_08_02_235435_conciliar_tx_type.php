<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConciliarTxType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         
        Schema::create('conciliar_tx_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->string('tx');
            $table->string('tendencia');
            $table->integer('banc_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conciliar_tx_type');
    }
}
