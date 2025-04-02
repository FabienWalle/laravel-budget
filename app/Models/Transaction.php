<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'sub_category',
        'custom_category',
        'description',
        'short_description',
        'amount',
        'type',
        'operation_date',
        'value_date',
        'user_id',];

    protected $casts = [
        'value_date' => 'date',
        'operation_date' => 'date',
        'amount' => 'integer',
    ];

    public function setAmountAttribute($value): void
    {
        $cleaned = is_numeric($value)
            ? (float) $value
            : (float) str_replace([' ', ','], ['', '.'], $value);

        $this->attributes['amount'] = (int) round($cleaned * 100);
    }

    public function getAmountAttribute($value): float
    {
        return $value / 100;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
