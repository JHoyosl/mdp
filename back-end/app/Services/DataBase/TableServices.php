<?php

namespace App\Services\DataBase;

use Illuminate\Support\Facades\Schema;

class TableServices
{

    public function createAccountingHeader(string $companyId)
    {

        $conciliationLocalHeadersTable = 'conciliationLocalHeader_' . $companyId;

        Schema::create($conciliationLocalHeadersTable, function ($table) {
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
}
