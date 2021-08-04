<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceGeneralHeader extends Model
{
    use SoftDeletes;

    const OPEN = 'open';
    const CLOSE = 'close';

    public function __construct($tableName){

        $this->table = $tableName;

    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        
        'fecha', 
        'file_name',
        'file_path',
        'status',
        'user',
        
    ];
}


function user(){

	return $this->hasOne(User::class, 'id','user');
}