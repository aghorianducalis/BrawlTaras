<?php

declare(strict_types=1);

namespace App\API\Exceptions;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

final class RequestException extends Exception
{
    private function __construct(string $message, int $code, ?Throwable $previous)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the original exception for additional context.
     */
    public function getOriginalException(): GuzzleException|Throwable
    {
        return $this->getPrevious();
    }

    /**
     * Create a RequestException from a GuzzleException.
     */
    public static function fromGuzzleException(GuzzleException $exception): self
    {
        return new self(
            message: $exception->getMessage(),
            code: $exception->getCode(),
            previous: $exception
        );
    }
}
