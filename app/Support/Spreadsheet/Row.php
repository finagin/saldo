<?php

namespace App\Support\Spreadsheet;

use App\Models\Saldo;
use App\Services\DateRecognize;
use App\Support\Enum\Saldo\CompareType;
use App\Support\Enum\SpreadsheetHeader;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as BaseRow;

class Row
{
    public Carbon $date;

    public string $nomination_number;

    public ?Carbon $nomination_date;

    public float $debit;

    public float $credit;

    public static array $cache = [];

    /**
     * @var array|array[]|string[][]
     */
    private array $raw;

    public function __construct(
        protected Spreadsheet $spreadsheet,
        protected BaseRow $row
    ) {
        $this->extractData()
            ->prepareDate()
            ->prepareDocument()
            ->prepareDebit()
            ->prepareCredit()
            ->colorize(0xFFFFFF);
    }

    public static function make(Spreadsheet $spreadsheet, BaseRow $row): static
    {
        $id = spl_object_id($row);

        if (! array_key_exists($id, static::$cache)) {
            static::$cache[$id] = new static($spreadsheet, $row);
        }

        return static::$cache[$id];
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    public function width(): string
    {
        $columns = array_reduce($this->spreadsheet->getColumns(), function ($carry, $columns) {
            foreach ($columns as $column) {
                preg_match('/(?P<min>[A-Za-z]+)(\d+)?:(?P<max>[A-Za-z]+)(\d+)?/', $column, $matches);
                $carry[] = mb_strtoupper($matches['min']);
                $carry[] = mb_strtoupper($matches['max']);
            }

            return $carry;
        }, []);

        usort($columns, fn ($a, $b) => static::charsToInt($a) <=> static::charsToInt($b));

        $min = array_shift($columns);
        $max = array_pop($columns);

        return $min.$this->row->getRowIndex().':'.$max.$this->row->getRowIndex();
    }

    public static function charsToInt(string $chars): int
    {
        $chars = str_split($chars);
        $carry = 0;

        foreach ($chars as $char) {
            $carry = $carry * 26 + ord($char) - 64;
        }

        return $carry;
    }

    private function extractData(): static
    {
        $this->raw = array_map(function ($coordinates) {
            return array_map(function ($coordinate) {
                preg_match('/^(?P<liter>[A-Za-z]+)/', $coordinate, $matches);

                return $this->row->getWorksheet()->getCell(
                    $matches['liter'].$this->row->getRowIndex()
                )->getFormattedValue();
            }, $coordinates);
        }, $this->spreadsheet->getColumns());

        return $this;
    }

    private function guessField(string $fieldName): string
    {
        return match (true) {
            is_string($this->raw[$fieldName]) => $this->raw[$fieldName],
            is_array($this->raw[$fieldName]) => array_values(array_filter(
                $this->raw[$fieldName],
                fn ($value): bool => $value !== '#NULL!'
            ))[0],
            default => throw new InvalidArgumentException(__('Invalid data type for '.$fieldName)),
        };
    }

    private static function prepareFloat($value): float
    {
        if (preg_match('/.\d{2}$/', $value)) {
            $value = str_replace([',', ' ', '_'], '', $value);
        }

        if (empty($value)) {
            return 0;
        }

        if ($result = filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return $result;
        }

        throw new InvalidArgumentException(__('Invalid data type for float'));
    }

    private function prepareDate(): static
    {
        $this->date = DateRecognize::make(
            $this->guessField(SpreadsheetHeader::DATE)
        );

        return $this;
    }

    private function prepareDocument(): static
    {
        $rawDocument = $this->guessField(SpreadsheetHeader::DOCUMENT);
        $this->nomination_date = DateRecognize::make($rawDocument);

        if (! preg_match("/(?P<number>[\d\/]+) от/", $rawDocument, $matches)) {
            throw new InvalidArgumentException(__('Invalid data type for document'));
        }

        $this->nomination_number = $matches['number'];

        return $this;
    }

    private function prepareDebit(): static
    {
        $rawDebit = $this->guessField(SpreadsheetHeader::DEBIT);

        $this->debit = static::prepareFloat($rawDebit);

        return $this;
    }

    private function prepareCredit(): static
    {
        $rawCredit = $this->guessField(SpreadsheetHeader::CREDIT);

        $this->credit = static::prepareFloat($rawCredit);

        return $this;
    }

    protected function getSaldo(): Saldo
    {
        return $this->spreadsheet->getSaldoFile()->saldo;
    }

    public function compareTo(self $row): bool
    {
        $result = $this->debit === $row->credit
            || $this->credit === $row->debit;

        foreach (CompareType::cases() as $compareType) {
            if ($this->getSaldo()->hasCompareType($compareType)) {
                $field = $this->getCompareTypeField($compareType);
                $result &= $this->{$field} == $row->{$field};
            }
        }

        return $result;
    }

    private function getCompareTypeField(CompareType $compareType): string
    {
        return match ($compareType) {
            CompareType::DATE => 'date',
            CompareType::DATE_BY_NOMINATION => 'nomination_date',
            CompareType::NUMBER_BY_NOMINATION => 'nomination_number',
        };
    }

    public function colorize(int $color = 0xE67C73): static
    {
        $this->row->getWorksheet()
            ->getStyle($this->width())
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB(dechex($color));

        return $this;
    }
}
