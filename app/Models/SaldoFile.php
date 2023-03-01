<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 * @property string $disk
 * @property string $path
 * @property \App\Models\Saldo $saldo
 */
class SaldoFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'disk',
        'path',
    ];

    protected $with = [
        'saldo',
    ];

    public function saldo(): BelongsTo
    {
        return $this->belongsTo(Saldo::class);
    }
}
