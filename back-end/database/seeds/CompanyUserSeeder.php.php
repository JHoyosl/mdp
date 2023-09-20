<?php

use Illuminate\Database\Seeder;

class CompanyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Schema::disableForeignKeyConstraints();
        DB::table('company_user')->truncate();
        Schema::enableForeignKeyConstraints();

        $company_user = array(

            array(
                'company_id' => '1',
                'user_id' => '1'
            ),
            array(
                'company_id' => '1',
                'user_id' => '3'
            ),

        );

        DB::table('company_user')->insert($company_user);
    }
}
