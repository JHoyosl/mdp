<?php

namespace App\Http\Resources\Company;

use App\Models\Company;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CompanyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function (Company $company) {
            return (new CompanyResource($company));
        });
    }
}
