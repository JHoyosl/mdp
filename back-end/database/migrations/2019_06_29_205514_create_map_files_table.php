<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id')->unsigned()->nullable();
            $table->integer('created_by')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->boolean('header');
            $table->text('description');
            $table->string('type');
            $table->text('map');
            $table->text('base');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('banks');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map_files');
    }
}
