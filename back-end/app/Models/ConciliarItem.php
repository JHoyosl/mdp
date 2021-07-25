<?php

namespace App\Models;

use App\Account;
use App\ConciliarHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConciliarItem extends Model
{
    use SoftDeletes;

    const OPEN_STATUS = 'OPEN';
    const CLOSE_STATUS = 'CLOSE';

    protected $table = "conciliar_items";
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
        'header_id', 
        'account_id',
        'debit_externo', 
        'debit_local',
        'credit_externo', 
        'credit_local',
        'balance_externo',
        'balance_local',
        'file_path',
        'file_name',
        'total',
        'status',
        
    ];

    function headers(){

    	return $this->belongsTo(ConciliarHeader::class, 'header_id');
    }

    function account(){

    	return $this->belongsTo(Account::class, 'account_id');
    }


}
