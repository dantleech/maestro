<?php

namespace Maestro\Extension\Runner\Loader;

use Maestro\Library\Instantiator\Instantiator;

final class Manifest
{
    /**
     * @var array
     */
    private $vars;

    /**
     * @var Package[]
     */
    private $packages = [];

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var array
     */
    private $env;

    public function __construct(array $vars = [], array $packages = [], string $path = null, array $env = [])
    {
        $this->vars = $vars;
        $this->path = $path;

        foreach ($packages as $name => $package) {
            $package['name'] = $name;
            $this->packages[] = Instantiator::create(Package::class, $package);
        }
        $this->env = $env;
    }

    public static function loadFromArray(array $manifest): self
    {
        return Instantiator::create(self::class, $manifest);
    }

    public function vars(): array
    {
        return $this->vars;
    }

    /**
     * @return Package[]
     */
    public function packages(): array
    {
        return $this->packages;
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function env(): array
    {
        return $this->env;
    }
}
