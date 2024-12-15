<?php

declare(strict_types=1);

namespace App\Services\Parser\Exceptions;

use Exception;
use Throwable;

final class ParsingException extends Exception
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
     * Creates a ParsingException from another exception.
     */
    public static function fromException(Throwable $exception): self
    {
        return new self(
            message: $exception->getMessage(),
            code: 422,
            previous: $exception
        );
    }
}
