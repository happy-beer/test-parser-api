<?php

namespace App\Console\Commands\Sync;

use App\Services\EntitySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SyncCycleCommand extends Command
{
    protected $signature = 'sync:cycle
        {entities?* : Entities to sync (orders sales incomes stocks)}
        {--from= : Start date in Y-m-d format}
        {--to= : End date in Y-m-d format}
        {--limit= : Records per page}
        {--test=0 : Test mode (1 = max 10 pages per entity)}';

    protected $description = 'Run full sync cycle for selected entities (or all by default)';

    /**
     * @var array<string, class-string<BaseSyncCommand>>
     */
    private array $entityCommandMap = [
        'orders' => SyncOrdersCommand::class,
        'sales' => SyncSalesCommand::class,
        'incomes' => SyncIncomesCommand::class,
        'stocks' => SyncStocksCommand::class,
    ];

    public function handle(EntitySyncService $syncService): int
    {
        try {
            $entities = $this->resolveEntities();
            $dateFrom = $this->resolveDateFrom();
            $dateTo = $this->resolveDateTo();
            $this->assertDateRange($dateFrom, $dateTo);

            $limit = $this->parseNullablePositiveIntOption('limit', 500);
            $testMode = $this->parseBooleanFlag('test');
            $maxPages = $testMode ? 10 : null;

            foreach ($entities as $entityKey) {
                $commandClass = $this->entityCommandMap[$entityKey];
                $command = app($commandClass);

                if (!$command instanceof BaseSyncCommand) {
                    throw new RuntimeException(sprintf('Invalid command mapping for entity "%s".', $entityKey));
                }

                $definition = $command->getSyncDefinition();

                if (!$definition['supportsDateTo']) {

                    $stockDateFrom = Carbon::today()->format('Y-m-d');

                    $this->line("Syncing {$definition['label']} with dateFrom={$stockDateFrom}" . ($testMode ? ' [test=10 pages]' : ''));

                    $result = $syncService->syncEntity(
                        endpoint: $definition['endpoint'],
                        modelClass: $definition['modelClass'],
                        uniqueColumns: $definition['uniqueColumns'],
                        dateFrom: $stockDateFrom,
                        dateTo: null,
                        limit: $limit,
                        startPage: 1,
                        maxPages: $maxPages
                    );
                } else {
                    $this->line("Syncing {$definition['label']} from {$dateFrom} to {$dateTo}" . ($testMode ? ' [test=10 pages]' : ''));

                    $result = $syncService->syncEntity(
                        endpoint: $definition['endpoint'],
                        modelClass: $definition['modelClass'],
                        uniqueColumns: $definition['uniqueColumns'],
                        dateFrom: $dateFrom,
                        dateTo: $dateTo,
                        limit: $limit,
                        startPage: 1,
                        maxPages: $maxPages
                    );
                }

                $this->info("{$definition['label']}: pages={$result['pages']}, fetched={$result['fetched']}, saved={$result['saved']}");
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @return array<int, string>
     */
    private function resolveEntities(): array
    {
        /** @var array<int, mixed> $provided */
        $provided = $this->argument('entities') ?? [];

        if ($provided === []) {
            return array_keys($this->entityCommandMap);
        }

        $entities = [];

        foreach ($provided as $item) {
            $entity = strtolower(trim((string)$item));

            if (!array_key_exists($entity, $this->entityCommandMap)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown entity "%s". Allowed: %s',
                    $entity,
                    implode(', ', array_keys($this->entityCommandMap))
                ));
            }

            $entities[$entity] = $entity;
        }

        return array_values($entities);
    }

    private function resolveDateFrom(): string
    {
        $from = $this->option('from');
        $date = is_string($from) && trim($from) !== ''
            ? trim($from)
            : (string)config('services.wb_api.earliest_date', '2020-01-01');

        return $this->validateDateFormat($date, 'from');
    }

    private function resolveDateTo(): string
    {
        $to = $this->option('to');
        $date = is_string($to) && trim($to) !== ''
            ? trim($to)
            : Carbon::today()->format('Y-m-d');

        return $this->validateDateFormat($date, 'to');
    }

    private function validateDateFormat(string $value, string $optionName): string
    {
        $date = Carbon::createFromFormat('Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException(sprintf('Option --%s must be in Y-m-d format.', $optionName));
        }

        return $value;
    }

    private function assertDateRange(string $from, string $to): void
    {
        if (Carbon::parse($to)->lt(Carbon::parse($from))) {
            throw new InvalidArgumentException('Option --to must be greater than or equal to --from.');
        }
    }

    private function parseNullablePositiveIntOption(string $name, ?int $max = null): ?int
    {
        $value = $this->option($name);

        if ($value === null || $value === '') {
            return null;
        }

        return $this->parsePositiveInt((string)$value, $name, $max);
    }

    private function parsePositiveInt(string $value, string $name, ?int $max = null): int
    {
        if (!ctype_digit($value) || (int)$value < 1) {
            throw new InvalidArgumentException(sprintf('Option --%s must be a positive integer.', $name));
        }

        $parsed = (int)$value;

        if ($max !== null && $parsed > $max) {
            throw new InvalidArgumentException(sprintf('Option --%s must be between 1 and %d.', $name, $max));
        }

        return $parsed;
    }

    private function parseBooleanFlag(string $name): bool
    {
        $value = (string)$this->option($name);
        $normalized = strtolower(trim($value));

        if (in_array($normalized, ['1', 'true', 'yes'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', ''], true)) {
            return false;
        }

        throw new InvalidArgumentException(sprintf(
            'Option --%s must be one of: 1, 0, true, false, yes, no.',
            $name
        ));
    }
}
