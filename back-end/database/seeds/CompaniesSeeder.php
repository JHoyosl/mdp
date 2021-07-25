<?php

use Illuminate\Database\Seeder;

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
    	);

    	DB::table('companies')->insert($companies);
    }
}
