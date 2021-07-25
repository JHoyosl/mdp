<?php

namespace App\Models;

use App\Models\Company;
use App\Models\AccessLog;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes, HasRoles;
    
    protected $dates = ['deleted_at'];

    const VERIFIED = 'true';
    const NO_VERIFIED = 'false';

    const SUPER_ADMIN = 'sadmin';
    const ADMIN = 'admin';
    const USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 
        'names', 
        'last_names', 
        'type',
        'current_company', 
        'verified',
        'verification_token',
        'remember_token',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];



    function companies(){

        return $this->belongsToMany(Company::class);
    }

    function accessLogs(){

        return $this->hasMany(AccessLog::class);
    }

    public static function genVerificationToken(){

        return Str::random(32);
    }

    function isVerified(){

        return $this->verified == VERIFIED;


    }

    public function findForPassport($username) {
        return $this->where('email', $username)->first();
    }
}   




















