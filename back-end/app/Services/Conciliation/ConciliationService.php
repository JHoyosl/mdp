<?php

namespace App\Services\Conciliation;

use App\Models\User;
use App\Models\Account;
use App\Models\ConciliarItem;
use App\Models\ConciliarHeader;
use Illuminate\Support\Facades\Schema;

class ConciliationService
{


    public function balanceCloseAccount($externalBalance, $localBalance, $accountId, $companyId)
    {
        $conciliarItemsTable = 'conciliar_items_' . $companyId;
        $conciliarHeaderTable = 'conciliar_headers_' . $companyId;

        $itemTable = new ConciliarItem($conciliarItemsTable);
        $headers = new ConciliarHeader($conciliarHeaderTable);

        $openHeader = $headers->where('status', ConciliarHeader::OPEN_STATUS)
            ->orderBy('id', 'desc')->first();


        $openItemTable = $itemTable->where('header_id', '=', $openHeader->id)
            ->where('account_id', '=', $accountId)
            ->first();

        $lastBalance = $this->getLastAccountBalance($companyId, $accountId);

        // return $this->localDifference();
        return [$externalBalance, $localBalance, $accountId, $companyId];
    }


    public function localDifference(ConciliarItem $itemsTable, $lastLocalBalance, $localBalance)
    {
        $calcValue = $lastLocalBalance + $itemsTable->debit_local - $itemsTable->credit_local;
        $diference = $calcValue - $localBalance;
        return $diference;
    }

    public function getLastAccountBalance($companyId, $accountId)
    {

        $conciliarHeaderTableName = 'conciliar_headers_' . $companyId;
        $conciliarItemsTableName = 'conciliar_items_' . $companyId;
        $conciliarHeaderTable = new ConciliarHeader($conciliarHeaderTableName);

        $conciliarHeaderClose = $conciliarHeaderTable->where('status', '=', ConciliarHeader::CLOSE_STATUS)
            ->orderBy('id', 'desc')
            ->first();

        $conciliarItemsTable = new ConciliarItem($conciliarItemsTableName);

        $conciliarItemsClose = $conciliarItemsTable
            ->where('header_id', '=', $conciliarHeaderClose->id)
            ->where('account_id', '=', $accountId)
            ->first();

        return [
            "balanceExterno" => $conciliarItemsClose->balance_externo,
            "balanceLocal" => $conciliarItemsClose->balance_local
        ];
    }
    // TABLE CREATION
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
