<?php

namespace Maestro\Loader;

final class Manifest
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var Package[]
     */
    private $packages = [];

    public function __construct(array $parameters = [], array $packages = [])
    {
        $this->parameters = $parameters;

        foreach ($packages as $name => $package) {
            $package['name'] = $name;
            $this->packages[] = Instantiator::create()->instantiate(Package::class, $package);
        }
    }

    public static function loadFromArray(array $manifest): self
    {
        return Instantiator::create()->instantiate(self::class, $manifest);
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return Package[]
     */
    public function packages(): array
    {
        return $this->packages;
    }
}
