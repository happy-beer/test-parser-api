<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EntitySyncService
{
    protected string $baseUrl;
    protected string $key;
    protected int $defaultLimit;
    protected int $timeout;
    protected int $pageSleepSeconds;

    public function __construct()
    {
        $this->baseUrl = rtrim((string)config('services.wb_api.base_url'), '/');
        $this->key = (string)config('services.wb_api.key');
        $this->defaultLimit = (int)config('services.wb_api.default_limit', 100);
        $this->timeout = (int)config('services.wb_api.timeout_seconds', 30);
        $this->pageSleepSeconds = max(0, (int)config('services.wb_api.page_sleep_seconds', 1));

        if ($this->baseUrl === '' || $this->key === '') {
            throw new RuntimeException('WB API credentials are not configured. Set WB_API_BASE_URL and WB_API_KEY.');
        }
    }

    /**
     * @param class-string<Model> $modelClass
     * @param array<int, string> $uniqueColumns
     * @return array{pages:int, fetched:int, saved:int}
     */
    public function syncEntity(
        string  $endpoint,
        string  $modelClass,
        array   $uniqueColumns,
        string  $dateFrom,
        ?string $dateTo = null,
        ?int    $limit = null,
        int     $startPage = 1,
        ?int    $maxPages = null
    ): array
    {

        $effectiveLimit = $limit ?: $this->defaultLimit;

        if ($effectiveLimit < 1 || $effectiveLimit > 500) {
            throw new RuntimeException(sprintf(
                'Invalid limit "%d". Allowed range is 1..500.',
                $effectiveLimit
            ));
        }

        $page = $startPage;
        $pages = 0;
        $fetched = 0;
        $saved = 0;

        /** @var Model $model */
        $model = new $modelClass;
        $table = $model->getTable();
        $allowedColumns = $model->getFillable();

        while (true) {
            $query = [
                'dateFrom' => $dateFrom,
                'page' => $page,
                'limit' => $effectiveLimit,
                'key' => $this->key,
            ];

            if ($dateTo !== null) {
                $query['dateTo'] = $dateTo;
            }

            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($this->baseUrl . '/' . $endpoint, $query);

            if (!$response->successful()) {
                throw new RuntimeException(sprintf(
                    'Request failed for %s (page %d): HTTP %d: %s',
                    $endpoint,
                    $page,
                    $response->status(),
                    $response->body()
                ));
            }

            $payload = $response->json();
            $items = is_array($payload['data'] ?? null) ? $payload['data'] : [];

            if ($items === []) {
                break;
            }

            $pages++;
            $fetched += count($items);

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalized = array_intersect_key($item, array_flip($allowedColumns));
                $identity = $this->buildIdentity($normalized, $uniqueColumns);

                if (count($identity) !== count($uniqueColumns)) {
                    continue;
                }

                if (DB::table($table)->updateOrInsert($identity, $normalized)) {
                    $saved++;
                }
            }

            if (count($items) < $effectiveLimit) {
                break;
            }

            if ($maxPages !== null && $pages >= $maxPages) {
                break;
            }

            if ($this->pageSleepSeconds > 0) {
                sleep($this->pageSleepSeconds);
            }

            $page++;
        }

        return [
            'pages' => $pages,
            'fetched' => $fetched,
            'saved' => $saved,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $uniqueColumns
     * @return array<string, mixed>
     */
    private function buildIdentity(array $row, array $uniqueColumns): array
    {
        $identity = [];

        foreach ($uniqueColumns as $column) {
            if (array_key_exists($column, $row)) {
                $identity[$column] = $row[$column];
            }
        }

        return $identity;
    }

}
