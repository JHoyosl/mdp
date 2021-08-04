<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocalTxType extends Model
{
    use SoftDeletes;
    
    protected $table = "conciliar_local_tx_type";
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
        'description', 
        'tx', 
        'company_id',
        'reference',
        'sign',
        
    ];

    function companies(){

    	return $this->belongsTo(Company::class, 'company_id');
    }

}
