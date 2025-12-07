<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class JournalTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'tag',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
