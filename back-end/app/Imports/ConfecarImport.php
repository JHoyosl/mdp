<?php

namespace App\Imports;

use App\ConfecarItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ConfecarImport implements ToCollection, WithCalculatedFormulas
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

       return new ConfecarItem([
            'B'=> $row[1],'F'=> $row[5],'G'=> $row[6],
            'I'=> $row[8],'J'=> $row[9],'N'=> $row[13],
            'O'=> $row[14],'Q'=> $row[16],'R'=> $row[17],
            'AG'=> $row[32],'AH'=> $row[33],'AI'=> $row[34],
            'AK'=> $row[36],'AL'=> $row[37],'AM'=> $row[38],
            'AN'=> $row[39],'AO'=> $row[40],'AP'=> $row[41],
            'AQ'=> $row[42],'AS'=> $row[44],'AW'=> $row[48],
            'AX'=> $row[49],'AY'=> $row[50],'AZ'=> $row[51],
            'BA'=> $row[52],'BB'=> $row[53],'BC'=> $row[54],
            'BD'=> $row[55],'BE'=> $row[56],'BH'=> $row[59],
            'BK'=> $row[62],'BN'=> $row[65],'BR'=> $row[69],
            'CJ'=> $row[87],'CK'=> $row[88],
            
       ]);
    }


}
