<?php

declare(strict_types=1);

namespace App\API\Exceptions;

use InvalidArgumentException;
use Throwable;

final class InvalidDTOException extends InvalidArgumentException
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
     * Creates an InvalidDTOException from a custom message.
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
     * Creates a InvalidDTOException from another exception.
     */
    public static function fromException(Throwable $exception): self
    {
        switch (get_class($exception)) {
            case InvalidArgumentException::class:
                $message = "Invalid DTO structure: " . $exception->getMessage();
                $code = 422;
                break;
            default:
                $message = "Unexpected Error: " . $exception->getMessage();
                $code = $exception->getCode() ?: 422;
                break;
        }

        return new self(
            message: $message,
            code: $code,
            previous: $exception
        );
    }
}
