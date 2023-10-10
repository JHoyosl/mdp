<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class BalanceClearedService
{

    public function createTables(int $companyId): bool
    {
        DB::beginTransaction();
        try {

            $this->createTableBlanceGeneralHeader($companyId);
            $this->createTableBlanceGeneralItems($companyId);
            $this->createTableConveniosHeaders($companyId);
            $this->createTableConveniosItems($companyId);
            $this->CreateTableConveniosCuentas($companyId);
            $this->CreateTableOperativoCuentas($companyId);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
        }
        return true;
    }

    public function populateOperativoMaster($data)
    {
    }

    public function createTableBlanceGeneralHeader(int $companyId)
    {

        $tableName = 'balance_general_headers_' . $companyId;
        Schema::dropIfExists($tableName);

        Schema::create($tableName, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('fecha');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status');
            $table->string('user');


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function createTableBlanceGeneralItems(int $companyId)
    {
        $tableName = 'balance_general_items_' . $companyId;
        $balanceGeneralHeader = 'balance_general_headers_' . $companyId;

        Schema::dropIfExists($tableName);

        Schema::create($tableName, function ($table) use ($balanceGeneralHeader) {
            $table->bigIncrements('id');
            $table->bigInteger('header_id')->unsigned();
            $table->integer('registro')->nullable();
            $table->string('agencia')->nullable();
            $table->string('cuenta')->nullable();
            $table->string('nombre_cuenta')->nullable();
            $table->decimal('saldo_anterior', 24, 2)->nullable();
            $table->decimal('debito', 24, 2)->nullable();
            $table->decimal('credito', 24, 2)->nullable();
            $table->decimal('saldo_actual', 24, 2)->nullable();


            $table->softDeletes();
            $table->timestamps();

            $table->foreign('header_id')->references('id')->on($balanceGeneralHeader);
        });
    }

    public function createTableConveniosHeaders(int $companyId)
    {
        $tableName = 'convenios_headers_' . $companyId;
        Schema::dropIfExists($tableName);

        Schema::create($tableName, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('fecha');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status');
            $table->string('user');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function createTableConveniosItems(int $companyId)
    {
        $tableName = 'convenios_items_' . $companyId;
        $convenioHeaders = 'convenios_headers_' . $companyId;
        Schema::dropIfExists($tableName);

        Schema::create($tableName, function ($table) use ($convenioHeaders) {
            $table->bigIncrements('id');
            $table->bigInteger('header_id')->unsigned();
            $table->string('numcon')->nullable();
            $table->string('codcon')->nullable();
            $table->string('nitcli')->nullable();
            $table->string('nropag')->nullable();
            $table->string('fecuo')->nullable();
            $table->decimal('vlrcuo', 24, 2)->nullable();
            $table->decimal('vlrpag', 24, 2)->nullable();
            $table->decimal('salcuo', 24, 2)->nullable();
            $table->string('fecaso')->nullable();
            $table->string('fecpag')->nullable();


            $table->softDeletes();
            $table->timestamps();

            $table->foreign('header_id')->references('id')->on($convenioHeaders);
        });
    }

    public function CreateTableConveniosCuentas(int $companyId)
    {
        $tableName = 'convenios_cuentas_' . $companyId;
        Schema::dropIfExists($tableName);

        Schema::create($tableName, function ($table) {
            $table->bigIncrements('id');
            $table->string('cuenta')->index();
            $table->string('linea');
            $table->string('nombre');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function CreateTableOperativoCuentas(int $companyId)
    {
        $tableName = 'operativo_cuentas_' . $companyId;
        Schema::dropIfExists($tableName);

        Schema::create($tableName, function ($table) {
            $table->bigIncrements('id');
            $table->string('cuenta')->index();
            $table->string('area');
            $table->string('descripcion');
            $table->string('naturaleza');
            $table->string('tipo_saldo');

            $table->softDeletes();
            $table->timestamps();
        });
    }
}
