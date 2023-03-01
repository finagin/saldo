<?php

namespace App\Support\Enum\Saldo;

enum CompareType: int
{
    case DATE = 2 ** 0;
    case NUMBER_BY_NOMINATION = 2 ** 1;
    case DATE_BY_NOMINATION = 2 ** 2;

    public function label(): string
    {
        return match ($this) {
            self::DATE => __('дат'),
            self::NUMBER_BY_NOMINATION => __('номера по наименованию'),
            self::DATE_BY_NOMINATION => __('дат по наименованию'),
        };
    }

    public static function cast(int $value): array
    {
        foreach (self::cases() as $case) {
            if ($value & $case->value) {
                $cases[] = $case;
            }
        }

        return $cases ?? [];
    }

    public static function castString(array $values, string $prefix = null): string
    {
        return implode(', ',
            array_filter(
                array_merge(
                    [$prefix],
                    array_map(fn ($case) => $case->label(), $values)
                )
            )
        );
    }
}
