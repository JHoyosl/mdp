<?php

use Illuminate\Support\Facades\DB;
use App\Models\HeaderAccountingInfo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeaderAccountingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('header_accounting_info', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('uploaded_by');
            $table->string('path');
            $table->string('file_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default(HeaderAccountingInfo::STATUS_OPEN);
            $table->integer('rows')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('header_accounting_info');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
