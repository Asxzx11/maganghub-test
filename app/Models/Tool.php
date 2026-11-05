<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    protected $fillable = [
        'name',
        'code',
        'category',
        'location',
        'current_condition',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function loanRecords(): HasMany
    {
        return $this->hasMany(LoanRecord::class);
    }
}
