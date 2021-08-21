<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocalTxTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('local_tx_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->string('tx');
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('reference');
            $table->string('sign');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('local_tx_types');
    }
}
