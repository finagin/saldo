<?php

namespace App\Models;

use App\Casts\Saldo\CompareType as CompareTypeCast;
use App\Support\Enum\Saldo\CompareType as CompareTypeEnum;
use App\Support\Enum\Saldo\Status as StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\SaldoFile[]|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection $files
 * @property \App\Support\Enum\Saldo\Status                                                                  $status
 * @property \App\Support\Enum\Saldo\CompareType[]                                                           $compare_type
 */
class Saldo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'status',
        'compare_type',
    ];

    protected $attributes = [
        'status' => StatusEnum::PENDING,
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'compare_type' => CompareTypeCast::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn (self $model) => $model->owner()->associate(auth()->id()));
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SaldoFile::class);
    }

    public function hasCompareType(CompareTypeEnum $type): bool
    {
        return in_array($type, $this->compare_type, true);
    }
}
