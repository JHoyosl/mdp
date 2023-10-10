<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait DbTools
{

    private function schemaExist(String $schemaName): bool
    {
        return Schema::hasTable($schemaName);
    }
}
