<?php

namespace App\Console\Commands\Sync;

use App\Models\Stock;

class SyncStocksCommand extends BaseSyncCommand
{
    protected string $label = 'Stocks';

    protected string $endpoint = 'api/stocks';

    protected string $modelClass = Stock::class;

    protected bool $supportsDateTo = false;

    /**
     * @var array<int, string>
     */
    protected array $uniqueColumns = ['date', 'warehouse_name', 'nm_id', 'barcode', 'tech_size'];

    protected $signature = 'sync:stocks
        {dateFrom? : Date in Y-m-d format}
        {--limit= : Records per page}
        {--page=1 : Start page}';

    protected $description = 'Sync stocks by date';

    protected function resolveDateRange(): array
    {
        $dateFrom = $this->validateRules(
            'dateFrom',
            $this->resolveRequiredArgument('dateFrom', 'Insert dateFrom (Y-m-d)'),
            'required|date_format:Y-m-d|after:yesterday|before:tomorrow'
        );

        return [$dateFrom, null];
    }
}
