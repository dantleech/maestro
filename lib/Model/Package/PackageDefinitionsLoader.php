<?php

namespace Maestro\Model\Package;

use Maestro\Model\Package\Exception\InvalidPackageDefinition;

class PackageDefinitionsLoader
{
    /**
     * @var array
     */
    private $prototypes;

    public function __construct(array $prototypes)
    {
        $this->prototypes = $prototypes;
    }

    public function load(array $packageDefinitions): PackageDefinitions
    {
        foreach ($packageDefinitions as $packageName => $packageDefinition) {
            if (!isset($packageDefinition['prototype'])) {
                continue;
            }

            $prototype = $packageDefinition['prototype'];

            if (!isset($this->prototypes[$prototype])) {
                throw new InvalidPackageDefinition(sprintf(
                    'Prototype "%s" specified by package "%s" does not exist, known prototypes "%s"',
                    $prototype,
                    $packageName,
                    implode('", "', array_keys($this->prototypes))
                ));
            }

            $packageDefinitions[$packageName] = array_merge($this->prototypes[$prototype], $packageDefinition);
        }

        return PackageDefinitions::fromArray($packageDefinitions);
    }
}
