<?php

namespace App\Http\Resources\TxType;

use App\Models\ExternalTxType;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ExternalTxTypeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function (ExternalTxType $mapFile) {
            return (new ExternalTxTypeResource($mapFile));
        });
    }
}
