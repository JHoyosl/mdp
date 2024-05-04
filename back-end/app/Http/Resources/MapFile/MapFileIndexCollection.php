<?php

namespace App\Http\Resources\MapFile;

use App\Models\MapFile;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MapFileIndexCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function (MapFile $mapFile) {
            return (new MapFileIndexResource($mapFile));
        });
    }
}
