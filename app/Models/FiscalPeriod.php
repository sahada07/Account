<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalPeriod extends Model
{
    //
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_closed'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean'
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
