<?php

namespace App\Http\Resources\MapFile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MapFileIndexCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->transform(function (MapFile $mapFile) {
            return (new MapFileIndexResource($mapFile));
        });
    }
}
