<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Invoice;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class Payment extends Model
{
    //

    use SoftDeletes;

    protected $fillable = [
        'payment_number',
        'amount',
        'payment_date',
        'payable_type',    
        'payable_id',
        'payment_method',
        'reference_number',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
         'amount' => 'decimal:2'
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }




}
