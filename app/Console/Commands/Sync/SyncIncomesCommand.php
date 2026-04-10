<?php

namespace App\Console\Commands\Sync;

use App\Models\Income;

class SyncIncomesCommand extends BaseSyncCommand
{
    protected string $label = 'Incomes';

    protected string $endpoint = 'api/incomes';

    protected string $modelClass = Income::class;

    /**
     * @var array<int, string>
     */
    protected array $uniqueColumns = ['income_id', 'date', 'nm_id', 'barcode'];

    protected $signature = 'sync:incomes
        {dateFrom? : Start date in Y-m-d format}
        {dateTo? : End date in Y-m-d format}
        {--limit= : Records per page}
        {--page=1 : Start page}';

    protected $description = 'Sync incomes by date range';
}
