<?php

namespace Maestro\Model\Package;

class PackageDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $initCommands;

    public function __construct(string $name, array $initCommands)
    {
        $this->name = $name;
        $this->initCommands = $initCommands;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function syncId(): string
    {
        return $this->name;
    }

    public function consoleId(): string
    {
        return $this->name;
    }

    public function repoUrl(): string
    {
        return sprintf('git@github.com:%s', $this->name());
    }

    public function dirName(): string
    {
        return str_replace('/', '-', $this->name());
    }

    public function initCommands(): array
    {
        return $this->initCommands;
    }
}
