<?php

namespace App\Http\Resources\Cuadres;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceSheetResource extends JsonResource
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
            'date' => $this->fecha,
            'fileName' => $this->file_name,
            'filePath' => $this->file_path,
            'status' => $this->status,
            'user' => $this->user
        ];
    }
}
