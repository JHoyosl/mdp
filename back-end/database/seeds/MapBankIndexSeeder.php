<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapLocalIndexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
	{
		\Schema::disableForeignKeyConstraints();
		\DB::table('map_bank_index')->truncate();
		\Schema::enableForeignKeyConstraints();

		$map_banc_index = array(

			array(
				'description' => 'FECHA DEL ARCHIVO',
				'type' => '0',
			),
			array(
				'description' => 'FECHA DEL MOVIMIENTO',
				'type' => '1',
			),
			array(
				'description' => 'NOMBRE TITULAR',
				'type' => '0',
			),
			array(
				'description' => 'IDENTIFICACION TITULAR',
				'type' => '0',
			),
			array(
				'description' => 'NUMERO DE CUENTA',
				'type' => '0',
			),
			array(
				'description' => 'TIPO DE CUENTA',
				'type' => '0',
			),
			array(
				'description' => 'CODIGO DE TRANSACCION',
				'type' => '0',
			),
			array(
				'description' => 'TIPO DE TRANSACCION/DESCRIPCION',
				'type' => '0',
			),
			array(
				'description' => 'NOMBRE DE TRANSACCION',
				'type' => '0',
			),
			array(
				'description' => 'REFERENCIA 1',
				'type' => '0',
			),
			array(
				'description' => 'VALOR DEBITO',
				'type' => '0',
			),
			array(
				'description' => 'REFERENCIA 2',
				'type' => '0',
			),
			array(
				'description' => 'REFERENCIA 3',
				'type' => '0',
			),
			array(
				'description' => 'CONSECUTIVO DE REGISTROS',
				'type' => '0',
			),
			array(
				'description' => 'NOMBRE OFICINA',
				'type' => '0',
			),
			array(
				'description' => 'CODIGO OFICINA',
				'type' => '0',
			),
			array(
				'description' => 'CANAL',
				'type' => '0',
			),
			array(
				'description' => 'NOMBRE PROVEEDOR',
				'type' => '0',
			),
			array(
				'description' => 'IDENTIFICACION DE PROVEEDOR',
				'type' => '0',
			),
			array(
				'description' => 'BANCO DESTINO',
				'type' => '0',
			),
			array(
				'description' => 'VALOR EN EFECTIVO',
				'type' => '0',
			),
			array(
				'description' => 'VALOR EN CHEQUE',
				'type' => '0',
			),
			array(
				'description' => 'NUMERO DE DOCUMENTO',
				'type' => '0',
			),
			array(
				'description' => 'FECHA DE RECHAZO',
				'type' => '0',
			),
			array(
				'description' => 'MOTIVO DE RECHAZO',
				'type' => '0',
			),
			array(
				'description' => 'VALOR CRÉDITO',
				'type' => '0',
			),
			array(
				'description' => 'VALOR (DEBITO/CREDITO)',
				'type' => '0',
			),
			array(
				'description' => 'CIUDAD',
				'type' => '0',
			),



		);

		DB::table('map_bank_index')->insert($map_banc_index);
	}
}
