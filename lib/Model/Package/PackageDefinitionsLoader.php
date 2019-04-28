<?php

namespace Maestro\Model\Package;

use Maestro\Model\Package\Exception\InvalidPackageDefinition;

class PackageDefinitionsLoader
{
    public function load(array $packageDefinitions, array $prototypes): PackageDefinitions
    {
        foreach ($packageDefinitions as $packageName => $packageDefinition) {
            if (!isset($packageDefinition['prototype'])) {
                continue;
            }

            $prototype = $packageDefinition['prototype'];

            if (!isset($prototypes[$prototype])) {
                throw new InvalidPackageDefinition(sprintf(
                    'Prototype "%s" specified by package "%s" does not exist, known prototypes "%s"',
                    $prototype, $packageName, implode('", "', array_keys($prototypes))
                ));
            }

            $packageDefinitions[$packageName] = array_merge($prototypes[$prototype], $packageDefinition);
        }

        return PackageDefinitions::fromArray($packageDefinitions);
    }
}
