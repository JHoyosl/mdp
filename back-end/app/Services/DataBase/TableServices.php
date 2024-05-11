<?php

namespace App\Services\DataBase;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TableServices
{

    public function createAccountingHeader(string $companyId)
    {

        $conciliationLocalHeadersTable = 'conciliationLocalHeader_' . $companyId;

        Schema::create($conciliationLocalHeadersTable, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uploadedBy')->unsigned();
            $table->string('filePath')->nullable();
            $table->string('fileName')->nullable();
            $table->integer('closedBy')->unsigned()->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->index(['status'])->default('created');

            $table->softDeletes();
            $table->timestamps();


            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('close_by')->references('id')->on('users');
        });
    }

    static function createLocalTxTypeTable(string $companyId)
    {
        $tableName = 'local_tx_types_' . $companyId;

        Schema::create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->string('tx');
            $table->string('reference');
            $table->string('sign');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
