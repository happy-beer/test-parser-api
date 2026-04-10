<?php

namespace App\Console\Commands\Sync;

use App\Services\EntitySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Throwable;

abstract class BaseSyncCommand extends Command
{
    protected string $label;

    protected string $endpoint;

    protected string $modelClass;

    /**
     * @var array<int, string>
     */
    protected array $uniqueColumns = [];

    public function handle(EntitySyncService $syncService): int
    {
        try {
            [$dateFrom, $dateTo] = $this->resolveDateRange();
            $limit = $this->parseNullablePositiveIntOption('limit', 500);
            $page = $this->parsePositiveIntOption('page');

            $result = $syncService->syncEntity(
                endpoint: $this->endpoint,
                modelClass: $this->modelClass,
                uniqueColumns: $this->uniqueColumns,
                dateFrom: $dateFrom,
                dateTo: $dateTo,
                limit: $limit,
                startPage: $page
            );

            $this->info("{$this->label} synced. Pages: {$result['pages']}, fetched: {$result['fetched']}, saved: {$result['saved']}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function resolveRequiredArgument(string $name, string $question): string
    {
        $value = $this->argument($name);

        if ($value === null || $value === '') {
            $value = $this->ask($question);
        }

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('Argument %s is required.', $name));
        }

        return $value;
    }

    protected function validateDateFormat(string $value, string $format): string
    {
        $date = Carbon::createFromFormat($format, $value);

        if ($date === false || $date->format($format) !== $value) {
            throw new InvalidArgumentException(sprintf('Invalid date "%s". Expected format: %s', $value, $format));
        }

        return $value;
    }

    /**
     * @param  string|array<int, string>  $rules
     */
    protected function validateRules(string $field, string $value, string|array $rules): string
    {
        $validator = Validator::make([$field => $value], [$field => $rules]);

        if ($validator->fails()) {
            $message = $validator->errors()->first($field);

            if ($message === '') {
                $message = sprintf('Invalid %s value.', $field);
            }

            throw new InvalidArgumentException($message);
        }

        return $value;
    }

    protected function parseNullablePositiveIntOption(string $name, ?int $max = null): ?int
    {
        $value = $this->option($name);

        if ($value === null || $value === '') {
            return null;
        }

        return $this->parsePositiveInt((string) $value, $name, $max);
    }

    protected function parsePositiveIntOption(string $name, ?int $max = null): int
    {
        return $this->parsePositiveInt((string) $this->option($name), $name, $max);
    }

    protected function parsePositiveInt(string $value, string $name, ?int $max = null): int
    {
        if (! ctype_digit($value) || (int) $value < 1) {
            throw new InvalidArgumentException(sprintf('Option --%s must be a positive integer.', $name));
        }

        $parsed = (int) $value;

        if ($max !== null && $parsed > $max) {
            throw new InvalidArgumentException(sprintf('Option --%s must be between 1 and %d.', $name, $max));
        }

        return $parsed;
    }

    protected function resolveDateRange(): array
    {
        $dateFrom = $this->validateDateFormat(
            $this->resolveRequiredArgument('dateFrom', 'Insert dateFrom (Y-m-d)'),
            'Y-m-d'
        );
        $dateTo = $this->validateDateFormat(
            $this->resolveRequiredArgument('dateTo', 'Insert dateTo (Y-m-d)'),
            'Y-m-d'
        );

        return [$dateFrom, $dateTo];
    }
}
