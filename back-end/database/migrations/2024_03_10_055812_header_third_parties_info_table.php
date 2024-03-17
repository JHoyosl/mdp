<?php

use App\Models\HeaderThirdPartiesInfo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HeaderThirdPartiesInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('header_third_parties_info', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('uploaded_by');
            $table->string('path');
            $table->string('file_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default(HeaderThirdPartiesInfo::STATUS_OPEN);
            $table->integer('rows')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts');
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
        Schema::dropIfExists('header_third_parties_info');
    }
}
