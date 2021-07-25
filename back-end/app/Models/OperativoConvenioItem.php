<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperativoConvenioItem extends Model
{
    

    public function __construct($tableName){

        $this->table = $tableName;

    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'header_id',
        'numcon',
        'codcon',
        'nitcli',
        'nropag',
        'fecuo',
        'vlrcuo',
        'vlrpag',
        'salcuo',
        'fecaso',
        'fecpag',
        
    ];
}
