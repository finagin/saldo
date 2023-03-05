<?php

namespace App\Support\Spreadsheet;

use App\Models\SaldoFile;
use App\Support\Enum\SpreadsheetHeader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Iterator;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet as BaseSpreadsheet;
use Throwable;

class Spreadsheet
{
    /**
     * @var array|array<string, array<int, string>>
     */
    protected array $columns = [
        SpreadsheetHeader::DATE => [],
        SpreadsheetHeader::DOCUMENT => [],
        SpreadsheetHeader::NOMINATION_NUMBER => [],
        SpreadsheetHeader::NOMINATION_DATE => [],
        SpreadsheetHeader::DEBIT => [],
        SpreadsheetHeader::CREDIT => [],
    ];

    protected BaseSpreadsheet $spreadsheet;

    protected ?int $startRow = null;

    /**
     * @var \App\Support\Spreadsheet\Row[]
     */
    protected array $rawCache = [];

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct(protected SaldoFile $file)
    {
        $this
            ->loadSpreadsheet()
            ->columnsRecognize()
            ->firstRowRecognize();
    }

    protected function loadSpreadsheet(): static
    {
        $this->spreadsheet = IOFactory::load(
            Storage::disk($this->file->disk)
                ->path($this->file->path)
        );

        return $this;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function columnsRecognize(): static
    {
        foreach ($this->getIterator() as $cell => $value) {
            if (array_key_exists(mb_strtolower($value), $this->columns)) {
                $this->columns[mb_strtolower($value)][] = static::getRange($cell, true);
            }
        }

        return $this;
    }

    protected function firstRowRecognize(): static
    {
        try {
            $this->startRow = $this->guessStartRow();
        } catch (Throwable) {
            $this->startRow = null;
        }

        return $this;
    }

    public function getIterator(callable $expression = null): Iterator
    {
        $expression ??= fn ($value): bool => true;
        $worksheet = $this->spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator($this->startRow ?? 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($expression($cell->getFormattedValue())) {
                    yield $cell => $cell->getFormattedValue();
                }
            }
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getRange(Cell $cell, bool $onlyLiters = false): string
    {
        $range = $cell->getMergeRange() ?: $cell->getCoordinate().':'.$cell->getCoordinate();

        return $onlyLiters
            ? preg_replace('/\d+/', '', $range)
            : $range;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Throwable
     */
    protected function guessStartRow(
        string $pattern = '/^\d{2}\.\d{2}\.\d{2}(:?\d{2})?$/',
        bool $forArray = false
    ): ?int {
        $candidate = null;
        $expression = fn ($value): bool => preg_match($pattern, $value);

        foreach ($this->getIterator($expression) as $cell => $value) {
            throw_unless(
                preg_match(
                    '/(?P<column>[A-Za-z]+)(?P<row>\d+)/',
                    $cell->getCoordinate(),
                    $current
                ),
                new Exception('Coordinate no longer exists')
            );

            switch (true) {
                case ($candidate['row'] ?? null) === $current['row']:
                    break;
                case ($candidate['column'] ?? null) === $current['column']:
                    return $candidate['row'] + ($forArray ? -1 : 0);
                default:
                    $candidate = Arr::only($current, ['column', 'row']);
            }
        }

        return null;
    }

    public function compare(self $spreadsheet): array
    {
        $diff = [];

        foreach ($spreadsheet->getRowIterator() as $firstRow) {
            foreach ($this->getRowIterator() as $secondRow) {
                if ($firstRow->compareTo($secondRow)) {
                    continue 2;
                }
            }

            $diff[] = $firstRow;
        }

        return $diff;
    }

    /**
     * @return \Iterator<\App\Support\Spreadsheet\Row>
     */
    public function getRowIterator(): Iterator
    {
        $worksheet = $this->spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator($this->startRow) as $row) {
            try {
                yield $this->rawCache[] = Row::make($this, $row);
            } catch (Throwable) {
                continue;
            }
        }
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getSaldoFile(): SaldoFile
    {
        return $this->file;
    }

    public function save(): bool
    {
        try {
            $writer = IOFactory::createWriter(
                $this->spreadsheet,
                'Xlsx'
            );

            $writer->save(
                Storage::disk($this->file->disk)
                    ->path($this->file->path)
            );
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
