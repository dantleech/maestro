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

    public static function fromArray(array $parameters): self
    {
        return new self($parameters);
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

    public function get($parameterName)
    {
        if (!array_key_exists($parameterName, $this->parameters)) {
            throw new ParameterDoesNotExist(sprintf(
                'Parameters "%s" does not exist, known parameters "%s"',
                $parameterName, implode('", "', array_keys($this->parameters))
            ));
        }
    }

    public function all(): array
    {
        return $this->parameters;
    }
}
