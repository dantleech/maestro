<?php

namespace Maestro\Loader;

use RuntimeException;

class Package
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $purgeWorkspace;

    /**
     * @var array
     */
    private $artifacts;

    /**
     * @var Loader[]
     */
    private $loaders = [];

    public function __construct(
        string $name,
        array $loaders = [],
        bool $purgeWorkspace = false,
        array $artifacts = []
    ) {
        $this->name = $name;

        foreach ($loaders as $name => $loader) {
            if (!isset($loader['type'])) {
                throw new RuntimeException(
                    '"type" key must be set for each loader'
                );
            }
            $classFqn = $loader['type'];
            unset($loader['type']);
            $this->loaders[$name] = Instantiator::create()->instantiate($classFqn, $loader);
        }

        $this->purgeWorkspace = $purgeWorkspace;
        $this->artifacts = $artifacts;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }

    public function artifacts(): array
    {
        return $this->artifacts;
    }

    /**
     * @return Loader[]
     */
    public function loaders(): array
    {
        return $this->loaders;
    }
}
