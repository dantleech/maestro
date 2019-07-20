<?php

namespace Maestro\Loader;

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
    private $environment;

    public function __construct(array $vars = [], array $packages = [], string $path = null, array $environment = [])
    {
        $this->vars = $vars;
        $this->path = $path;

        foreach ($packages as $name => $package) {
            $package['name'] = $name;
            $this->packages[] = Instantiator::create()->instantiate(Package::class, $package);
        }
        $this->environment = $environment;
    }

    public static function loadFromArray(array $manifest): self
    {
        return Instantiator::create()->instantiate(self::class, $manifest);
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

    public function environment(): array
    {
        return $this->environment;
    }
}
