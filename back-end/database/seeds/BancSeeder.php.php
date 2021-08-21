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

    		array('cod_comp' =>'1', 'name' =>'Banco de Bogotá', 'nit' =>'860002964-4', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'2', 'name' =>'Banco Popular', 'nit' =>'860007738-9', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'6', 'name' =>'Banco CorpBanca', 'nit' =>'890903937-0', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'7', 'name' =>'Bancolombia', 'nit' =>'890903938-8', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'9', 'name' =>'Citibank', 'nit' =>'860051135-4', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'12', 'name' =>'Banco GNB Sudameris', 'nit' =>'860050750-1', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'13', 'name' =>'BBVA Colombia', 'nit' =>'860003020-1', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'23', 'name' =>'Banco de Occidente', 'nit' =>'890300279-4', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'32', 'name' =>'Banco Caja Social S.A.', 'nit' =>'860007335-4', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'51', 'name' =>'Banco Davivienda', 'nit' =>'860034313-7', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'19', 'name' =>'Banco Colpatria', 'nit' =>'860034594-1', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'40', 'name' =>'Banagrario', 'nit' =>'800037800-8', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'52', 'name' =>'AV Villas', 'nit' =>'860035827-5', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>null, 'name' =>'Credifinanciera S.A.', 'nit' =>'900200960-9', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'59', 'name' =>'Bancamía S.A.', 'nit' =>'900215071-1', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'53', 'name' =>'Banco W S.A.', 'nit' =>'900378212-2', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'61', 'name' =>'Bancoomeva', 'nit' =>'900406150-5', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'63', 'name' =>'Finandina', 'nit' =>'860051894-6', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'62', 'name' =>'Banco Falabella S.A.', 'nit' =>'900047981-8', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'60', 'name' =>'Banco Pichincha S.A.', 'nit' =>'890200756-7', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'66', 'name' =>'Coopcentral', 'nit' =>'890203088-9', 'currency' =>'COP', 'portal' =>'1'),
            array('cod_comp' =>'65', 'name' =>'Banco Santander', 'nit' =>'900628110-3', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>null, 'name' =>'Banco Mundo Mujer S.A.', 'nit' =>'900768933-8', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>null, 'name' =>'Mibanco S.A.', 'nit' =>'860025971-5', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>'69', 'name' =>'Banco Serfinanza S.A.', 'nit' =>'860043186-6', 'currency' =>'COP', 'portal' =>'0'),
            array('cod_comp' =>null, 'name' =>'Banco J.P. Morgan Colombia S.A., (la "Sociedad")', 'nit' =>'900114346-8', 'currency' =>'COP', 'portal' =>'0'),
    		
    	);

    	DB::table('banks')->insert($bancs);
    }
}
