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

    /**
     * @var array
     */
    private $files;

    public function __construct(
        string $name,
        array $initCommands = [],
        string $url = null,
        array $files = []
    )
    {
        $this->name = $name;
        $this->initCommands = $initCommands;
        $this->url = $url;
        $this->files = $files;
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
        return $this->url ? $this->url : 'git@github.com:/'.$this->name;
    }

    public function files(): array
    {
        return $this->files;
    }
}
