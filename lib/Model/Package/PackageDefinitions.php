<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;
use Maestro\Model\Package\PackageBuilder;
use Maestro\Model\Package\PackageDefinitionBuilder;
use Maestro\Model\Package\PackageDefinitions;

class PackageDefinitions implements IteratorAggregate
{
    /**
     * @var array
     */
    private $packages;

    public function __construct(array $packages)
    {
        $this->packages = $packages;
    }

    public static function fromArray(array $definitions): PackageDefinitions
    {
        $packages = [];
        foreach ($definitions as $packageName => $definition) {
            $packages[] = PackageDefinitionBuilder::create($packageName)->build();
        }

        return new self($packages);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->packages);
    }
}
