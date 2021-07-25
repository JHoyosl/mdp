<?php

namespace App\Imports;

use App\OperativoConvenioItem;
use Maatwebsite\Excel\Concerns\ToModel;

class OperativoConvenioImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new OperativoConvenioItem([]);
    }
}
