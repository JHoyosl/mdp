<?php

namespace App\Http\Resources\Bank;

use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'codComp' => $this->cod_comp,
            'nit' => $this->nit,
            'name' => $this->name,
            'currency' => $this->currency,
            'portal' => $this->portal,

        ];
    }
}
