<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConciliarValues extends Model
{
    use SoftDeletes;

    protected $table = "conciliar_values";
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
        'status',
        
    ];
}
