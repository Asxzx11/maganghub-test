<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRecord extends Model
{
    protected $fillable = [
        'tool_id',
        'technician_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'return_condition',
        'return_notes',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
