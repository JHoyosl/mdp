<?php

namespace App\Http\Resources\TxType;

use Illuminate\Http\Resources\Json\JsonResource;

class LocalTxTypeResource extends JsonResource
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
            'id'            => $this->id,
            'description'   => $this->description,
            'tx'            => $this->tx,
            'companyId'     => $this->company_id,
            'reference'     => $this->reference,
            'sign'          => $this->sign,
            'deletedAt'     => $this->deleted_at,
            'createdAt'     => $this->created_at,
            'updatedAt'     => $this->updated_at,
        ];
    }
}
