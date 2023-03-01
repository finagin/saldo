<?php

namespace App\Services;

use App\Support\Enum\DateFormats;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

abstract class DateRecognize
{
    public static function make(string $raw)
    {
        $separator = '(?:[\s\.\/-])';
        $day = '(\d{1,2})';
        $month = '(?:(\d{1,2}|[A-Za-zА-Яа-я]{3,})\.?)';
        $year = '(\d{2}(?:\d{2})?)';

        $format = implode($separator, [
            $day,
            $month,
            $year,
        ]);

        if (! mb_eregi($format, $raw, $matches)) {
            throw new InvalidArgumentException('Invalid date format');
        }

        [, $day, $month, $year] = $matches;

        $month = self::guessMonth($month);
        $year = self::guessYear($year);

        if (! $month) {
            throw new InvalidArgumentException('Month not found');
        }

        return Carbon::create($year, $month, $day);
    }

    public static function guessMonth($month): ?int
    {
        if (preg_match('/^(?P<month>0?[1-9]|1[0-2])$/', $month, $matches)) {
            return (int) $matches['month'];
        }

        $month = str_replace('.', '', mb_strtolower($month));
        $monthsList = [
            DateFormats::RU_SHORT,
            DateFormats::RU_LONG[0],
            DateFormats::RU_LONG[1],
        ];

        foreach ($monthsList as $monthList) {
            if ($index = array_search(mb_strtolower($month), $monthList)) {
                return $index + 1;
            }
        }

        return null;
    }

    private static function guessYear(string $year): int
    {
        if (preg_match('/^(?P<year>19\d{2}|20\d{2})$/', $year, $matches)) {
            return (int) $matches['year'];
        }

        if (preg_match('/^(?P<year>\d{2})$/', $year, $matches)) {
            return (int) $matches['year'] + 2000;
        }

        throw new InvalidArgumentException('Invalid year format');
    }
}
