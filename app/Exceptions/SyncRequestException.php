<?php

namespace App\Exceptions;

use RuntimeException;

class SyncRequestException extends RuntimeException
{
    public function __construct(
        public readonly string $endpoint,
        public readonly int $page,
        public readonly int $status,
        public readonly string $responseBody
    ) {
        parent::__construct(sprintf(
            'Request failed for %s (page %d): HTTP %d: %s',
            $endpoint,
            $page,
            $status,
            $responseBody
        ));
    }
}
