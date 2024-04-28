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
     * Get the original exception for additional context.
     */
    public function getOriginalException(): InvalidArgumentException|Throwable
    {
        return $this->getPrevious();
    }

    /**
     * Create a InvalidDTOException from a InvalidArgumentException.
     */
    public static function fromInvalidArgumentException(InvalidArgumentException $exception): self
    {
        return new self(
            message: $exception->getMessage(),
            code: $exception->getCode(),
            previous: $exception
        );
    }

    /**
     * Create a InvalidDTOException from an exception message as a string.
     */
    public static function fromString(string $message): self
    {
        $exception = new InvalidArgumentException(message: $message);

        return InvalidDTOException::fromInvalidArgumentException(exception: $exception);
    }
}
