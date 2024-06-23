<?php

namespace App\Http\Resources\Cuadres;

use App\Models\BalanceGeneralHeader;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BalanceSheetCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function (BalanceGeneralHeader $header) {
            return new BalanceSheetResource($header);
        });
    }
}
