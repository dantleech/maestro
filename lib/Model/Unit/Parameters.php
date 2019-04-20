<?php

namespace Maestro\Model\Unit;

final class Parameters
{
    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function mergeArray(array $parameters): void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function copy(): self
    {
        return new self($this->parameters);
    }

    public static function new()
    {
        return new self([]);
    }
}
