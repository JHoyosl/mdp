<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocalTxType extends Model
{
    use SoftDeletes;

    const SIMPLE_TYPE = 'SIMPLE';
    const COMPUESTO_TYPE = 'COMPUESTO';

    protected $table = "conciliar_local_tx_type";
    protected $dates = ['deleted_at'];


    public function __construct()
    {
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
        'type',

    ];

    function companies()
    {

        return $this->belongsTo(Company::class, 'company_id');
    }
}
