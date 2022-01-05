<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
    	DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();
        
    	$users = array(

    		array(
                'email' => 'jhoyosl@globalsys.co',
                'names' => 'gsys',
    			'last_names' => 'gsys',
    			'type' => 'sadmin',
    			'current_company' => '1',
    			'verified' => 'false',
    			'verification_token' => 'Cej7xslRPO5THzmdsgy4R5II5ftoBcPO',
    			'password' => '$2y$10$prP5NA6GmdablmQfuPAzaO1FfPO5jDvOgFpdkkcHW2rS57jh.KSj2',

    		),

            array(
                'email' => 'sara.pulgarin@globalsys.co',
                'names' => 'Sara',
                'last_names' => 'Pulgarin',
                'type' => 'user',
                'current_company' => '1',
                'verified' => 'false',
                'verification_token' => 'Cej7xslRPO5THzmdsgy4R5II5ftoBcPO',
                'password' => '$2y$10$prP5NA6GmdablmQfuPAzaO1FfPO5jDvOgFpdkkcHW2rS57jh.KSj2',
            
            ),
            
            array(
                'email' => 'gerencia@mejoradeprocesos.com.co',
                'names' => 'Rodrigo',
                'last_names' => '',
                'type' => 'sadmin',
                'current_company' => '1',
                'verified' => 'false',
                'verification_token' => 'Cej7xslRPO5THzmdsgy4R5II5ftoBcPO',
                'password' => '$2y$10$prP5NA6GmdablmQfuPAzaO1FfPO5jDvOgFpdkkcHW2rS57jh.KSj2',
            
            ),
    	);

    	DB::table('users')->insert($users);
    }
}
