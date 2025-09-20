<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Schema::disableForeignKeyConstraints();
        DB::table('companies')->truncate();
        Schema::enableForeignKeyConstraints();

    	$companies = array(


				// SOLO ADMIN 
			array('nit' => '830142376-7',
    			'name' => 'GLOBALSYS',
    			'sector' => '6721',
    			'address' => 'CRA 24 NO 13-52',
    			'location_id' => '2',
    			'phone' => '33356333',

    		),
    	);

    	DB::table('companies')->insert($companies);
    }
}
