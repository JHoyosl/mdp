<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\User;
use App\Models\Account;
use App\Models\Location;
use App\Models\ConciliarHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
   use SoftDeletes;
    
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nit', 
        'name', 
        'sector', 
        'address',
        'phone', 
        'location_id', 
        'map_id', 
        
    ];

    function conciliaciones(){

        return $this->hasMany(ConciliarHeader::class);
    }

    function locations(){

        return $this->hasMany(Location::class,'id','location_id');
    }

    function users(){

    	return $this->belongsToMany(User::class);
    }
   
    function banks(){

    	return $this->belongsToMany(Bank::class);
    }

    function accounts(){

    	return $this->hasMany(Account::class, 'company_id');
    }

    function location(){

        return $this->hasMany(Location::class);
    }
}
