<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Notebook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
        'icon',
        'order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
