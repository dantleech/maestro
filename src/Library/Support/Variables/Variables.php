<?php

namespace Maestro\Library\Support\Variables;

class Variables
{
    /**
     * @var array
     */
    private $variables;

    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }
}
