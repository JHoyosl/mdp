<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\ConciliarItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConciliarHeader extends Model
{
    use SoftDeletes;
    
    protected $table = "conciliar_headers";
    protected $dates = ['deleted_at'];

    const OPEN_STATUS = 'OPEN';
    const CLOSE_STATUS = 'CLOSE';
    

    public function __construct($tableName){

        $this->table = $tableName;

    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fecha_ini', 
        'fecha_end', 
        'created_by',
        'close_by',
        'status',
        'file_name',
        'file_path',
        
    ];

    function usersCreate(){

    	return $this->hasOne(User::class, 'id','created_by');
    }

    function usersClose(){

        return $this->hasOne(User::class, 'id','close_by');
    }

}
