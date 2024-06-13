<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConciliarValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conciliar_external_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tx_type_id')->unsigned()->nullable();
            $table->integer('map_id')->unsigned();
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('valor_credito')->nullable();
            $table->string('valor_debito')->nullable();
            $table->string('valor_debito_credito')->nullable();
            $table->dateTime('fecha_movimiento')->nullable();
            $table->string('codigo_tx')->nullable();
            $table->string('referencia_1')->nullable();
            $table->string('referencia_2')->nullable();
            $table->string('referencia_3')->nullable();
            $table->string('fecha_archivo')->nullable();
            $table->string('nombre_titular')->nullable();
            $table->string('identificacion_titula')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('nombre_transaccion')->nullable();
            $table->string('consecutivo_registro')->nullable();
            $table->string('nombre_oficina')->nullable();
            $table->string('codigo_oficina')->nullable();
            $table->string('canal')->nullable();
            $table->string('nombre_proveedor')->nullable();
            $table->string('id_proveedor')->nullable();
            $table->string('banco_destino')->nullable();
            $table->string('fecha_rechazo')->nullable();
            $table->string('motivo_rechazo')->nullable();
            $table->string('ciudad')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // $table->foreign('tx_type_id')->references('id')->on('conciliar_external_tx_type');
            // $table->foreign('conciliar_items_id')->references('item_id')->on('conciliar_items_');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conciliar_external_values');
    }
}
