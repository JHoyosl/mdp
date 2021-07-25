<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NaturalezaCuentas extends Model
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
        
        'cuenta', 
        'area',
        'descripcion',
        'naturaleza',
        'tipo_saldo',
        
    ];
}
			