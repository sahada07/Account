<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Customer extends Model
{
    //

    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
