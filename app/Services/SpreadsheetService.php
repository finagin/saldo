<?php

namespace App\Services;

use App\Models\Saldo;
use App\Models\SaldoFile;
use App\Support\Spreadsheet\Spreadsheet;
use Exception;
use Illuminate\Support\Collection;

class SpreadsheetService
{
    /**
     * @var \Illuminate\Support\Collection|\PhpOffice\PhpSpreadsheet\Spreadsheet[]
     */
    private Collection|array $spreadsheets;

    /**
     * @throws \Throwable
     */
    public function __construct(protected Saldo $saldo)
    {
        $this
            ->loadSpreadsheets();
    }

    /**
     * @throws \Throwable
     */
    public static function make(): static
    {
        return new static(...func_get_args());
    }

    /**
     * @throws \Throwable
     */
    protected function loadSpreadsheets(): static
    {
        throw_if(
            $this->saldo->files->count() < 2,
            new Exception('Not enough files')
        );

        $this->spreadsheets = $this->saldo->files
            ->map(fn (SaldoFile $file) => new Spreadsheet($file));

        return $this;
    }

    public function compare(): Collection
    {
        /**
         * @var \App\Support\Spreadsheet\Spreadsheet $first
         * @var \App\Support\Spreadsheet\Spreadsheet $second
         */
        [$first, $second] = $this->spreadsheets;

        return collect($first->compare($second))
            ->merge($second->compare($first));
    }
}
