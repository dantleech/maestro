<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;
use Maestro\Model\Instantiator;

class PackageDefinitions implements IteratorAggregate
{
    /**
     * @var PackageDefinition[]
     */
    private $packages;

    private function __construct(array $packages)
    {
        $this->packages = $packages;
    }

    public static function fromArray(array $definitions): PackageDefinitions
    {
        $packages = [];
        foreach ($definitions as $packageName => $definition) {
            $definition['name'] = $packageName;
            $package = Instantiator::create()->instantiate(PackageDefinition::class, $definition);
            $packages[$packageName] = $package;
        }

        return new self($packages);
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

    public function get(string $name): PackageDefinition
    {
        if (!isset($this->packages[$name])) {
            throw new RuntimeException(sprintf(
                'Could not find package definition with name "%s", known packages "%s"',
                $name,
                implode('", "', $this->names())
            ));
        }

        return $this->packages[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->packages);
    }
}
