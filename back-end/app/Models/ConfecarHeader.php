<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfecarHeader extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];


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
        'created_by',
        'status',
        'file_name',
        'file_path',
        
    ];


}
