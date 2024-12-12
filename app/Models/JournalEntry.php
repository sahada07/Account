<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'entry_date',
        'reference_number',
        'description',
        'fiscal_period_id',
        'status',
        'created_by'
    ];

    protected $casts = [
        'entry_date' => 'date'
    ];

    public function fiscalPeriod()
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
