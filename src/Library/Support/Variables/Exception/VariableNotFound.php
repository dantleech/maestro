<?php

namespace Maestro\Library\Support\Variables\Exception;

use RuntimeException;

class VariableNotFound extends RuntimeException
{
    public function __construct(string $variable, array $availableVariables)
    {
        parent::__construct(sprintf(
            'Variable "%s" not found, known variables "%s"',
            $variable,
            implode('", "', $availableVariables)
        ));
    }
}
