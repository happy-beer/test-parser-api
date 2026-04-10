<?php

namespace App\Console\Commands\Sync;

use App\Models\Sale;

class SyncSalesCommand extends BaseSyncCommand
{
    protected string $label = 'Sales';

    protected string $endpoint = 'api/sales';

    protected string $modelClass = Sale::class;

    /**
     * @var array<int, string>
     */
    protected array $uniqueColumns = ['sale_id', 'g_number', 'date', 'nm_id', 'barcode'];

    protected $signature = 'sync:sales
        {dateFrom? : Start date in Y-m-d format}
        {dateTo? : End date in Y-m-d format}
        {--limit= : Records per page}
        {--page=1 : Start page}';

    protected $description = 'Sync sales by date range';
}
