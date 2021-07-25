<?php

use Illuminate\Database\Seeder;

class map_local_index extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       		Schema::disableForeignKeyConstraints();
	        DB::table('map_local_index')->truncate();
	        Schema::enableForeignKeyConstraints();

	        $map_local_index = array(
    		array(
    			'description' => 'FECHA DE MOVIMIENTO','type' => '1',
    		),
    		array(
    			'description' => 'NOMBRE AGENCIA','type' => '0',
    		),
    		array(
    			'description' => 'NOMBRE CENTRO DE COSTOS','type' => '0',
    		),
    		array(
    			'description' => 'CODIGO CENTRO DE COSTOS','type' => '0',
    		),
    		array(
    			'description' => 'NUMERO DE COMPROBANTE','type' => '0',
    		),
    		array(
    			'description' => 'NOMBRE USUARIO','type' => '0',
    		),
    		array(
    			'description' => 'DESCRIPCION','type' => '1',
    		),
    		array(
    			'description' => 'REFERENCIA 1','type' => '1',
    		),
    		array(
    			'description' => 'VALOR (DEBITO/CREDITO)','type' => '0',
    		),
    		array(
    			'description' => 'SALDO ANTERIOR','type' => '0',
    		),
    		array(
    			'description' => 'SALDO ACTUAL','type' => '1',
    		),
    		array(
    			'description' => 'NUMERO CUENTA CONTABLE','type' => '1',
    		),
    		array(
    			'description' => 'NOMBRE CUENTA CONTABLE','type' => '0',
    		),
    		array(
    			'description' => 'REFERENCIA 2','type' => '0',
    		),
    		array(
    			'description' => 'REFERENCIA 3','type' => '0',
    		),
    		array(
    			'description' => 'NOMBRE DE TERCERO','type' => '0',
    		),
    		array(
    			'description' => 'IDENTIFICACION DE TERCERO','type' => '0',
    		),
    		array(
    			'description' => 'VALOR CRÉDITO','type' => '0',
    		),
    		array(
    			'description' => 'VALOR DEBITO','type' => '0',
    		),
    		array(
    			'description' => 'CODIGO USUARIO','type' => '0',
    		),
    		array(
    			'description' => 'NUMERO DE CUENTA BANCARIA','type' => '0',
    		),
    		array(
    			'description' => 'FECHA INGRESO','type' => '0',
    		),
    		array(
    			'description' => 'VALOR CRÉDITO','type' => '0',
    		),
    		array(
    			'description' => 'OFICINA ORIGEN','type' => '0',
    		),
    		array(
    			'description' => 'OFICINA DESTINO','type' => '0',
    		),
    	
    	);

	    DB::table('map_local_index')->insert($map_local_index);
    }
}
