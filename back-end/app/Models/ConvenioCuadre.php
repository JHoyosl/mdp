<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConvenioCuadre extends Model
{
    use SoftDeletes;

    // protected $table = "conciliar_values";
    // protected $dates = ['deleted_at'];

    public function __construct($tableName){

        $this->table = $tableName;

    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cuenta', 
        'linea',
        'nombre', 
        
    ];
}
