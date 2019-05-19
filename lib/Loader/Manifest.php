<?php

namespace Maestro\Loader;

use Maestro\Loader\Exception\PrototypeNotFound;

final class Manifest
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var Prototype[]
     */
    private $prototypes = [];

    /**
     * @var Package[]
     */
    private $packages = [];

    public function __construct(array $parameters = [], array $prototypes = [], array $packages = [])
    {
        $this->parameters = $parameters;

        foreach ($prototypes as $name => $prototype) {
            $prototype['name'] = $name;
            $this->prototypes[$name] = Instantiator::create()->instantiate(Prototype::class, $prototype);
        }

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
     * @return Prototype[]
     */
    public function prototypes(): array
    {
        return $this->prototypes;
    }

    /**
     * @return Package[]
     */
    public function packages(): array
    {
        return $this->packages;
    }

    public function prototype(string $name): Prototype
    {
        if (!isset($this->prototypes[$name])) {
            throw new PrototypeNotFound(sprintf(
                'Could not find prototype "%s", known prototypes "%s"',
                $name, implode('", "', array_keys($this->prototypes))
            ));
        }

        return $this->prototypes[$name];
    }
}
