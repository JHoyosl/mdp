<?php

namespace App\Http\Resources\MapFile;

use App\Http\Resources\Bank\BankResource;
use App\Http\Resources\Company\CompanyResource;
use App\Http\Resources\User\UserResource;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MapFileIndexResource extends JsonResource
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
            'bank' => $this->bank ? new BankResource($this->bank) : NULL,
            'company' => new CompanyResource($this->company),
            'header' => $this->header,
            'description' => $this->description,
            'createdBy' => new UserResource($this->createdBy),
            'type' => $this->type,
            'map' => json_decode($this->map),
            'base' => json_decode($this->base),
            'separator' => $this->separator,
            'extension' => $this->extension,
            'skipTop' => $this->skip_top,
            'skipBottom' => $this->skip_bottom,
            'dateFormat' => $this->date_format,
        ];
    }
}
