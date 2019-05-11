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

    /**
     * @var array
     */
    private $depends;

    public function __construct(string $name, string $type, array $parameters = [], array $depends = [])
    {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->name = $name;
        $this->depends = $depends;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function depends(): array
    {
        return $this->depends;
    }
}
