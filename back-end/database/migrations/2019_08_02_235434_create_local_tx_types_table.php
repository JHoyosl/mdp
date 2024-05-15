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
        Schema::create('reconciliation_local_tx_types_[companyId]', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->string('tx');
            $table->string('reference');
            $table->string('sign');
            $table->softDeletes();
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
        Schema::dropIfExists('reconciliation_local_tx_types');
    }
}
