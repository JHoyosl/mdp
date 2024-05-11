<?php

namespace App\Http\Resources\TxType;

use App\Http\Resources\Bank\BankResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalTxTypeResource extends JsonResource
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
            'reference'     => $this->reference,
            'type'          => $this->type,
            'sign'          => $this->sign,
            'deletedAt'     => $this->deleted_at,
            'createdAt'     => $this->created_at,
            'updatedAt'     => $this->updated_at,
            'bank' => new BankResource($this->banks),
        ];
    }
}
