<?php

declare(strict_types=1);

namespace App\API\Exceptions;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Throwable;

final class ResponseException extends Exception
{
    private function __construct(string $message, int $code, ?Throwable $previous)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the original exception for debugging or context.
     */
    public function getOriginalException(): ?Throwable
    {
        return $this->getPrevious();
    }

    /**
     * Creates a ResponseException from a custom message.
     */
    public static function fromMessage(string $message, int $code = 400): self
    {
        return new self(
            message: $message,
            code: $code,
            previous: null
        );
    }

    /**
     * Creates a ResponseException from another exception.
     */
    public static function fromException(Throwable $exception): self
    {
        switch (get_class($exception)) {
            case GuzzleException::class:
                $message = "API Request Error: " . $exception->getMessage();
                $code = $exception->getCode() ?: 500;
                break;
            case InvalidArgumentException::class:
                $message = "Invalid API Response: " . $exception->getMessage();
                $code = 422;
                break;
            default:
                $message = "Unexpected Error: " . $exception->getMessage();
                $code = $exception->getCode() ?: 500;
                break;
        }

        return new self(
            message: $message,
            code: $code,
            previous: $exception
        );
    }
}
