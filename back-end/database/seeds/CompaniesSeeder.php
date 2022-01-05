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

    		array('nit' => '860007327-5',
    			'name' => 'FINCOMERCIO',
    			'sector' => '6711',
    			'address' => 'CARRERA 12B # 8A-30 PISO 11',
    			'location_id' => '1',
    			'phone' => '3078330',

    		),
    		array('nit' => '830143476-7',
    			'name' => 'COOPERATIVA UNIMOS',
    			'sector' => '6711',
    			'address' => 'CRA 23 NO 13-52',
    			'location_id' => '2',
    			'phone' => '33333333',

    		),

			array('nit' => '830142376-7',
    			'name' => 'GLOBALSYS',
    			'sector' => '6721',
    			'address' => 'CRA 24 NO 13-52',
    			'location_id' => '4',
    			'phone' => '33356333',

    		),
    	);

    	DB::table('companies')->insert($companies);
    }
}
