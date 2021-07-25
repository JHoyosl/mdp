<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfecarItems extends Model
{
    
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
        'A', 'B','C','D','F','G','H','I','J',
        'K','L','M','N','O','P','Q','R','S',
        'T','U','V','W','X','Y','Z',
        'AA', 'AB','AC','AD','AF','AG','AH','AI','AJ',
        'AK','AL','AM','AN','AO','AP','AQ','AR','AS',
        'AT','AU','AV','AW','AX','AY','AZ',
        'BA', 'BB','BC','BD','BF','BG','BH','BI','BJ',
        'BK','BL','BM','BN','BO','BP','BQ','BR','BS',
        'BT','BU','BV','BW','BX','BY','BZ',
        'CA', 'CB','CC','CD','CF','CG','CH','CI','CJ',
        'CK',
        
    ];
}
