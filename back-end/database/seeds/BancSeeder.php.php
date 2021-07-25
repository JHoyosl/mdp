<?php

use Illuminate\Database\Seeder;

class BancSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banks')->delete();

    	$bancs = array(

    		array(
    			'cod_comp' => '1',
    			'name' => 'BANCO DE BOGOTA',
    			'nit' => '860002964-4',
    			'currency' => 'COP',
    			'portal' => '1',
    		),
    		array(
    			'cod_comp' => '2',
    			'name' => 'BANCO POPULAR',
    			'nit' => '860007738-9',
    			'currency' => 'COP',
    			'portal' => '1',
    		),
    		array(
    			'cod_comp' => '6',
    			'name' => 'BANCO ITAU',
    			'nit' => '890903937-2',
    			'currency' => 'COP',
    			'portal' => '1',
    		),
    		array(
    			'cod_comp' => '7',
    			'name' => 'BANCOLOMBIA',
    			'nit' => '890903938-8',
    			'currency' => 'COP',
    			'portal' => '1',
    		),
    		array(
    			'cod_comp' => '13',
    			'name' => 'BANCO BBVA',
    			'nit' => '860003020-1',
    			'currency' => 'COP',
    			'portal' => '1',
    		),
    		array(
    			'cod_comp' => '19',
    			'name' => 'BANCO COLPATRIA',
    			'nit' => '860034594-1',
    			'currency' => 'COP',
    			'portal' => '1',
    		),
    		
    	);

    	DB::table('banks')->insert($bancs);
    }
}
