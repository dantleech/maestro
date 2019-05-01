<?php

namespace Maestro\Model\Package;

class ManifestItem
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, string $type, array $parameters = [])
    {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->name = $name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
