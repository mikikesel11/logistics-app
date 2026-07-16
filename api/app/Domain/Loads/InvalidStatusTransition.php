<?php

namespace App\Domain\Loads;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when a load status change violates the lifecycle. Renders as a 422
 * through the API envelope (HttpExceptionInterface is mapped in bootstrap/app.php).
 */
class InvalidStatusTransition extends HttpException
{
    public function __construct(LoadStatus $from, LoadStatus $to)
    {
        parent::__construct(
            422,
            "Cannot transition a load from '{$from->value}' to '{$to->value}'.",
        );
    }
}
