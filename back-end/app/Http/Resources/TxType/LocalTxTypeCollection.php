<?php

namespace App\Http\Resources\TxType;

use App\Models\LocalTxType;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LocalTxTypeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function (LocalTxType $localTxType) {
            return (new LocalTxTypeResource($localTxType));
        });
    }
}
