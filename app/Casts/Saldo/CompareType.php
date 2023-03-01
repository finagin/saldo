<?php

namespace App\Casts\Saldo;

use App\Support\Enum\Saldo\CompareType as CompareTypeEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CompareType implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        foreach (CompareTypeEnum::cases() as $case) {
            if ($value & $case->value) {
                $cases[] = $case;
            }
        }

        return $cases ?? [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        return array_reduce(
            $value,
            fn (int $carry, CompareTypeEnum $item) => $carry | $item->value,
            0
        );
    }
}
