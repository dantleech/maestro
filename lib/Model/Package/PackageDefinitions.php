<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;
use Maestro\Model\Package\Exception\InvalidPackageDefinition;

class PackageDefinitions implements IteratorAggregate
{
    const KEY_INITIALIZE = 'initialize';

    /**
     * @var array
     */
    private $packages;

    public function __construct(array $packages)
    {
        $this->packages = $packages;
    }

    public function names(): array
    {
        return array_map(function (PackageDefinition $definition) {
            return $definition->name();
        }, $this->packages);
    }

    public function query(?string $query)
    {
        return new self(array_values(array_filter($this->packages, function (PackageDefinition $definition) use ($query) {
            if (empty($query)) {
                return true;
            }

            if (strtolower($query) === strtolower($definition->name())) {
                return true;
            }

            if (false !== strpos($query, '*')) {
                $query = str_replace('*', '__WILDCARD__', $query);
                $query = preg_quote($query);
                $query = str_replace('__WILDCARD__', '.*', $query);
                return preg_match('{' . $query . '}', $definition->name());
            }

            return false;
        })));
    }

    public static function fromArray(array $definitions): PackageDefinitions
    {
        $packages = [];
        foreach ($definitions as $packageName => $definition) {
            $definition = self::validateDefinition($definition);
            $packageBuilder = PackageDefinitionBuilder::create($packageName);

            if ($definition[self::KEY_INITIALIZE]) {
                $packageBuilder = $packageBuilder->withInitCommands($definition[self::KEY_INITIALIZE]);
            }

            $packages[] = $packageBuilder->build();
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

    private static function validateDefinition(array $definition): array
    {
        $defaults = [
            self::KEY_INITIALIZE => [],
        ];

        if ($diff = array_diff(array_keys($definition), array_keys($defaults))) {
            throw new InvalidPackageDefinition(sprintf(
                'Unexpected keys "%s", allowed keys: "%s"',
                implode('", "', $diff),
                implode('", "', array_keys($definition))
            ));
        }

        return array_merge($defaults, $definition);
    }
}
