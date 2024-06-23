<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgreementsMaster extends Model
{
    use SoftDeletes;

    // protected $table = "conciliar_values";
    // protected $dates = ['deleted_at'];

    public function __construct($tableName)
    {

        $this->table = $tableName;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'line',
        'name',

    ];
}
