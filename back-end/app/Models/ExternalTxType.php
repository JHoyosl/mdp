<?php

namespace App\Models;

use App\Bank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalTxType extends Model
{
    use SoftDeletes;
    
    const SIMPLE_TYPE = 'SIMPLE';
    const COMPUESTO_TYPE = 'COMPUESTO';
    protected $dates = ['deleted_at'];


     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description', 
        'tx', 
        'bank_id',
        'reference',
        'type',
        'sign',
        
    ];

    function banks(){

    	return $this->belongsTo(Bank::class, 'bank_id');
    }

    
}
