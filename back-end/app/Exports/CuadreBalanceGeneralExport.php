<?php

namespace App\Exports;

use App\Exports\Sheets\CuadreBalanceSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


/**
 * 
 */
class CuadreBalanceGeneralExport implements WithMultipleSheets
{

	use Exportable;

	private $fecha;
	private $nombre;
	private $info;
	
	function __construct($fecha, $info)
	{
		$this->fecha = $fecha;
		$this->info = $info;
	}

	/**
     * @return array
     * __construct($fecha, $nombre, $info){
     */ 
    public function sheets(): array
    {
        $sheets = [];

         $sheets[] = new CuadreBalanceSheet($this->fecha, 'balanceItems', $this->info['balanceItems']);
         $sheets[] = new CuadreBalanceSheet($this->fecha, 'nautralezaContable', $this->info['nautralezaContable']);
         $sheets[] = new CuadreBalanceSheet($this->fecha, 'nautralezaOperativa', $this->info['nautralezaOperativa']);

        return $sheets;
    }
}