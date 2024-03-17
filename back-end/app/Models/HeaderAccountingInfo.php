<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Accounting;

class HeaderAccountingInfo extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'header_accounting_info';

    const STATUS_CREATED = 'CREATED';
    const STATUS_OPEN = 'OPEN';
    const STATUS_DELETED = 'DELETED';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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

    static function items($headerId)
    {
        return AccountingItems::where('header_id', $headerId);
    }
}
