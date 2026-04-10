<?php

namespace App\Console\Commands\Sync;

use App\Models\Order;

class SyncOrdersCommand extends BaseSyncCommand
{
    protected string $label = 'Orders';

    protected string $endpoint = 'api/orders';

    protected string $modelClass = Order::class;

    /**
     * @var array<int, string>
     */
    protected array $uniqueColumns = ['g_number', 'date', 'nm_id', 'barcode', 'odid'];

    protected $signature = 'sync:orders
        {dateFrom? : Start date in Y-m-d format}
        {dateTo? : End date in Y-m-d format}
        {--limit= : Records per page}
        {--page=1 : Start page}';

    protected $description = 'Sync orders by date range';
}
