<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementsHeader extends Model
{


    const OPEN = 'open';
    const CLOSE = 'close';

    public function __construct()
    {
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'status',
        'user',
    ];
}
