<?php


namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;

class CuadreBalanceSheet implements FromArray, WithTitle, ShouldAutoSize
{

	private $fecha;
	private $nombre;
	private $info;

	public function __construct($fecha, $nombre, $info){

		$this->fecha = $fecha;
		$this->nombre = $nombre;
		$this->info = $info;
	}


	/**
     * @return Builder
     */
	public function array(): array
    {
        return [
            $this->info
        ];
    }

     /**
     * @return string
     */
    public function title(): string
    {
        return $this->nombre.'_'.$this->fecha;
    }

    
}