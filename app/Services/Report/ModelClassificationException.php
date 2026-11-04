<?php

namespace App\Services\Report;

use RuntimeException;

class ModelClassificationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status,
    ) {
        parent::__construct($message);
    }
}
