<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{

    const CHECKING_ACCOUNT = 'checking';
    const SAVING_ACCOUNT = 'saving';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->string('acc_type');
            $table->string('bank_account');
            $table->string('local_account');
            $table->integer('map_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('banks');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('map_id')->references('id')->on('map_files');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
