<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalTxTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_tx_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->string('tx');
            $table->integer('bank_id')->unsigned()->nullable();
            $table->string('reference');
            $table->string('type')->default('SIMPLE');
            $table->string('sign');
                
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('banks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('external_tx_types');
    }
}
