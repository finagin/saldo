<?php

namespace App\Jobs\Saldo;

use App\Models\Saldo;
use App\Services\SpreadsheetService;
use App\Support\Enum\Saldo\Status;
use App\Support\Spreadsheet\Row;
use App\Support\Spreadsheet\Spreadsheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Throwable;

class CompareJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60 * 60;

    /**
     * The unique ID of the job.
     *
     * @used-by \Illuminate\Bus\UniqueLock::getKey to generate the lock key for the given job.
     */
    public function uniqueId(): string
    {
        return $this->saldo->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Saldo $saldo,
    ) {
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->setStatus(Status::PROCESSING);

        $this->saldo->refresh();

        SpreadsheetService::make($this->saldo)
            ->compare()
            ->map(fn (Row $row) => $row->colorize())
            ->reduce(function (Collection $carry, Row $row) {
                $carry[spl_object_id($row->getSpreadsheet())] = $row->getSpreadsheet();

                return $carry;
            }, collect())
            ->each(fn (Spreadsheet $spreadsheet) => $spreadsheet->save());

        $this->setStatus(Status::COMPLETED);
    }

    protected function setStatus(Status $status): void
    {
        $this->saldo->update(compact('status'));
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->setStatus(Status::FAILED);
        logger()->emergency($exception->getMessage(), [
            'exception' => $exception,
        ]);
    }
}
