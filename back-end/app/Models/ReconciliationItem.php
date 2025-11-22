<?php

namespace App\Models;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReconciliationItem extends Model
{

    const OPEN_STATUS = 'OPEN';
    const CLOSE_STATUS = 'CLOSE';
    const DONE_STATUS = 'DONE';


    const TYPE_PARTIAL = 'PARTIAL';
    const TYPE_CLOSE = 'CLOSE';
    const TYPE_INIT = 'INIT';

    const STEP_UPLOADED = 'UPLOADED';
    const STEP_SET_BALANCE = 'SET_BALANCE';
    const STEP_MANUAL = 'MANUAL';
    const STEP_DONE = 'DONE';

    protected $table = "conciliar_items";
    protected $dates = ['deleted_at'];

    public function __construct($tableName = null)
    {

        $this->table = $tableName;
        
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'process',
        'start_date',
        'end_date',
        'external_debit',
        'external_credit',
        'local_debit',
        'local_credit',
        'external_balance',
        'local_balance',
        'difference',
        'status',
        'step',
        'type',
        'updated_at',
        'created_at',
    ];

    function account()
    {
        return $this->belongsTo(Account::class, 'account_id')->with('banks');
    }
}
