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
     * @var Manifest
     */
    private $manifest;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $prototype;

    public function __construct(
        string $name,
        array $initCommands = [],
        string $url = null,
        array $manifest = [],
        array $parameters = [],
        string $prototype = null
    )
    {
        $this->name = $name;
        $this->initCommands = $initCommands;
        $this->url = $url;
        $this->manifest = Manifest::fromArray($manifest);
        $this->parameters = $parameters;
        $this->prototype = $prototype;
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

    /**
     * @return Manifest<ManifestItem>
     */
    public function manifest(): Manifest
    {
        return $this->manifest;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function prototype(): string
    {
        return $this->prototype;
    }
}
