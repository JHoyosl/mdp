<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'nit' => $this->nit,
            'name' => $this->name,
            'sector' => $this->sector,
            'address' => $this->address,
            'phone' => $this->phone,
            'locationId' => $this->location_id,
            'mapId' => $this->map_id,
        ];
    }
}
