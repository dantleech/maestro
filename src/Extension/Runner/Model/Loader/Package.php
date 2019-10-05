<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Maestro\Library\Instantiator\Instantiator;

class Package
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @var bool
     */
    private $purgeWorkspace;

    /**
     * @var array
     */
    private $vars;

    /**
     * @var array
     */
    private $env;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var string[]
     */
    private $tags = [];

    public function __construct(
        string $name,
        array $tasks = [],
        bool $purgeWorkspace = false,
        array $vars = [],
        array $env = [],
        ?string $url = null,
        ?string $version = null,
        array $tags = []
    ) {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::instantiate(Task::class, $task);
        }

        $this->purgeWorkspace = $purgeWorkspace;
        $this->vars = $vars;
        $this->env = $env;
        $this->url = $url;
        $this->version = $version;
        $this->tags = $tags;
    }

    /**
     * @return Task[]
     */
    public function tasks(): array
    {
        return $this->tasks;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function env()
    {
        return $this->env;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function tags(): array
    {
        return $this->tags;
    }
}
