<?php

namespace App\Services\Reconciliation;

use App\Models\User;
use App\Models\Account;
use App\Models\ConciliarItem;
use Illuminate\Support\Facades\Schema;

class ReconciliationService
{

    public function createTmpTableConciliarItems(String $companyId)
    {
        $tmpItems = 'conciliar_tmp_items_' . $companyId;

        Schema::create($tmpItems, function ($table) {
            $table->increments('id');
            $table->integer('header_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->decimal('debit_externo', 24, 2);
            $table->decimal('credit_externo', 24, 2);
            $table->decimal('debit_local', 24, 2);
            $table->decimal('credit_local', 24, 2);
            $table->decimal('balance_externo', 24, 2);
            $table->decimal('balance_local', 24, 2);
            $table->decimal('total', 24, 2);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('created');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function createTmpTableConciliarExternalValues(String $companyId)
    {
        $tmpExternalValues = 'conciliar_tmp_external_values_' . $companyId;
        Schema::dropIfExists($this->$tmpExternalValues);

        Schema::create($tmpExternalValues, function ($table) {
            $table->bigIncrements('id');
            $table->boolean('matched')->default(false);
            $table->integer('tx_type_id')->unsigned();
            $table->string('tx_type_name')->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->string('descripcion')->comment = 'transaccion/descripcion';
            $table->string('operador')->nullable();
            $table->decimal('valor_credito', 24, 2)->nullable();
            $table->decimal('valor_debito', 24, 2)->nullable();
            $table->decimal('valor_debito_credito', 24, 2)->nullable();
            $table->dateTime('fecha_movimiento')->nullable();
            $table->dateTime('fecha_archivo')->nullable();
            $table->string('codigo_tx')->nullable();
            $table->string('referencia_1')->nullable();
            $table->string('referencia_2')->nullable();
            $table->string('referencia_3')->nullable();
            $table->string('nombre_titular')->nullable();
            $table->string('identificacion_titular')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('nombre_transaccion')->nullable();
            $table->string('consecutivo_registro')->nullable();
            $table->string('nombre_oficina')->nullable();
            $table->string('codigo_oficina')->nullable();
            $table->string('canal')->nullable();
            $table->string('nombre_proveedor')->nullable();
            $table->string('id_proveedor')->nullable();
            $table->string('banco_destino')->nullable();
            $table->dateTime('fecha_rechazo')->nullable();
            $table->string('motivo_rechazo')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('tipo_cuenta')->nullable();
            $table->string('numero_documento')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
