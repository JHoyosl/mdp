<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderThirdPartiesInfo extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'header_third_parties_info';

    const STATUS_CREATED = 'CREATED';
    const STATUS_OPEN = 'OPEN';
    const STATUS_DELETED = 'DELETED';

    protected $fillable = [
        'account_id',
        'company_id',
        'uploaded_by',
        'path',
        'file_name',
        'start_date',
        'end_date',
        'rows',
        'status',

    ];

    function uploadedBy()
    {
        return $this->belongsTo(User::class);
    }

    function company()
    {
        return $this->belongsTo(Company::class);
    }
}
