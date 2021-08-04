<?php

namespace App\Models;

use App\Models\BalanceGeneralHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceGeneralItem extends Model
{
    use SoftDeletes;

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
        'registro',
        'agencia',
        'cuenta',
        'nombre_cuenta',
        'saldo_anterior',
        'debito',
        'credito',
        'saldo_actual',
        
    ];
    
}


function headers(){

	return $this->belongsTo(BalanceGeneralHeader::class, 'header_id');
}
