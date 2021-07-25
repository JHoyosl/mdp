<?php

namespace App\Imports;

use App\BalanceGeneralItem;
use Maatwebsite\Excel\Concerns\ToModel;

class BalanceGeneralImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
       return new BalanceGeneralItem([]);
    }
}
