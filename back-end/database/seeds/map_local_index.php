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
				'description' => 'FECHA DE MOVIMIENTO', 'type' => '1',
			),
			array(
				'description' => 'DESCRIPCION', 'type' => '1',
			),
			array(
				'description' => 'CUENTA EXTERNA', 'type' => '0',
			),
			array(
				'description' => 'REFERENCIA 1', 'type' => '1',
			),
			array(
				'description' => 'REFERENCIA 2', 'type' => '0',
			),
			array(
				'description' => 'REFERENCIA 3', 'type' => '0',
			),
			array(
				'description' => 'OTRA REFERENCIA', 'type' => '0',
			),
			array(
				'description' => 'SALDO ACTUAL', 'type' => '0',
			),
			array(
				'description' => 'VALOR DEBITO', 'type' => '0',
			),
			array(
				'description' => 'SALDO ANTERIOR', 'type' => '0',
			),
			array(
				'description' => 'VALOR CREDITO', 'type' => '0',
			),
			array(
				'description' => 'CODIGO USUARIO', 'type' => '0',
			),
			array(
				'description' => 'NOMBRE AGENCIA', 'type' => '0',
			),
			array(
				'description' => 'VALOR (Debito/Credito)', 'type' => '0',
			),
			array(
				'description' => 'NOMBRE CENTRO DE COSTOS', 'type' => '0',
			),
			array(
				'description' => 'CODIGO CENTRO DE COSTOS', 'type' => '0',
			),
			array(
				'description' => 'NUMERO DE COMPROBANTE', 'type' => '0',
			),
			array(
				'description' => 'NOMBRE DE USUARIO', 'type' => '0',
			),
			array(
				'description' => 'NOMBRE CUENTA CONTABLE', 'type' => '0',
			),
			array(
				'description' => 'NUMERO CUENTA CONTABLE', 'type' => '0',
			),
			array(
				'description' => 'NOMBRE DE TERCERO', 'type' => '0',
			),
			array(
				'description' => 'IDENTIFICACION DE TERCERO', 'type' => '0',
			),
			array(
				'description' => 'FECHA INGRESO', 'type' => '0',
			),
			array(
				'description' => 'FECHA ORIGEN', 'type' => '0',
			),
			array(
				'description' => 'OFICINA ORIGEN', 'type' => '0',
			),
			array(
				'description' => 'OFICINA DESTINO', 'type' => '0',
			),
			array(
				'description' => 'NUMERO LOTE', 'type' => '0',
			),
			array(
				'description' => 'CONSECUTIVO LOTE', 'type' => '0',
			),
			array(
				'description' => 'TIPO DE REGISTRO', 'type' => '0',
			),
			array(
				'description' => 'AMBIENTE ORIGEN', 'type' => '0',
			),
			array(
				'description' => 'BENEFICIARIO', 'type' => '0',
			),

		);

		DB::table('map_local_index')->insert($map_local_index);
	}
}
