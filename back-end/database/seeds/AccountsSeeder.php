<?php

use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->delete();

        $accounts = array(

            array('bank_id' =>4, 'acc_type' =>'checking', 'bank_account' =>'039-137051-24', 'local_account' =>'111005100012', 'company_id' =>1, 'map_id'=>null),
            array('bank_id' =>4, 'acc_type' =>'checking', 'bank_account' =>'039-356629-46', 'local_account' =>'111005100025', 'company_id' =>1, 'map_id'=>null),
            array('bank_id' =>8, 'acc_type' =>'checking', 'bank_account' =>'242057271', 'local_account' =>'111005100003', 'company_id' =>1, 'map_id'=>null),
            array('bank_id' =>8, 'acc_type' =>'checking', 'bank_account' =>'265056630', 'local_account' =>'111005100024', 'company_id' =>1, 'map_id'=>null),

        );

        DB::table('accounts')->insert($accounts);

    }
}
