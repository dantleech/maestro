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

    /**
     * @var string
     */
    private $url;

    public function __construct(string $name, array $initCommands, string $url)
    {
        $this->name = $name;
        $this->initCommands = $initCommands;
        $this->url = $url;
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

    public function dirName(): string
    {
        return str_replace('/', '-', $this->name());
    }

    public function initCommands(): array
    {
        return $this->initCommands;
    }

    public function url(): string
    {
        return $this->url;
    }
}
