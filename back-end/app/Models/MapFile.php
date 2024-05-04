<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;

class MapFile extends Model
{
    //use SoftDeletes;
    // protected $table = 'centers';
    protected $dates = ['deleted_at'];

    const TYPE_EXTERNAL = 'external';
    const TYPE_INTERNAL = 'internal';

    const TYPE_CONCILIAR_INTERNO = 'conciliar_interno';
    const TYPE_CONCILIAR_EXTERNO = 'conciliar_externo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'bank_id',
        'company_id',
        'header',
        'description',
        'created_by',
        'type',
        'map',
        'base',
        'separator',
        'extension',


    ];

    function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    function accounts()
    {

        return $this->belongsTo(Account::class, 'account_id');
    }

    function createdBy()
    {

        return $this->belongsTo(User::class, 'created_by');
    }

    function bank()
    {

        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
