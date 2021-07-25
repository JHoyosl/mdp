<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperativoConvenioHeader extends Model
{
    

    const OPEN = 'open';
    const CLOSE = 'close';

    public function __construct($tableName){

        $this->table = $tableName;

    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        
        'fecha', 
        'file_name',
        'file_path',
        'status',
        'user',
        
    ];
}
