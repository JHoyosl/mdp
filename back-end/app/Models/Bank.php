<?php

namespace App\Models;

use App\MapFile;
use App\ExternalTxType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{

    use SoftDeletes;
    
    protected $table = "banks";
    protected $dates = ['deleted_at'];

    const PORTAL = 'true';
    const NO_PORTAL = 'false';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cod_comp', 
        'nit', 
        'name', 
        'currency', 
        'portal',
        
        
    ];


    function companies(){

    	return $this->belongsToMany(Company::class);
    }

    function account(){

    	return $this->hasMany(Account::class);
    }

    function TxTypes(){

        return $this->hasMany(ExternalTxType::class, 'banc_id');

    }

}
