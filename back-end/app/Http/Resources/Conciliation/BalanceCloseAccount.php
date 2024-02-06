<?php

namespace App\Http\Resources\Conciliation;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceCloseAccount extends JsonResource
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
            'externalBalance' => $request->externalBalance,
            'localBalance' => $request->localBalance,
            'accountId' => $request->accountId,
        ];
    }
}
