<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;
use Maestro\Model\Package\Exception\InvalidPackageDefinition;
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
            self::validateDefinition($definition);
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

    private static function validateDefinition(array $definition): void
    {
        $keys = [
            'initialize',
        ];

        if ($diff = array_diff(array_keys($definition), $keys)) {
            throw new InvalidPackageDefinition(sprintf(
                'Unexpected keys "%s", allowed keys: "%s"',
                implode('", "', $diff), implode('", "', array_keys($definition))
            ));
        }
    }
}
