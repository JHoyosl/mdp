<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\Company;
use App\Models\MapFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{

    use SoftDeletes;
    
    const CHECKING_ACCOUNT = 'checking';
    const SAVING_ACCOUNT = 'saving';

    protected $dates = ['deleted_at'];
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bank_id', // banco
        'acc_type',  //tipo de cuenta? ahorro,..?
        'bank_account', //cuenta del banco
        'local_account',//cuenta de la empresa?
        'company_id',//empresa de la cuenta
        'map_id'//
        
    ];


    function map(){

        return $this->hasOne(MapFile::class, 'id');

    }

    function banks(){

    	return $this->belongsTo(Bank::class, 'bank_id');
    }

    function companies(){

    	return $this->belongsTo(Company::class, 'company_id');
    }

}
