<?php

namespace Maestro\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Exception\PrototypeNotFound;
use Maestro\Extension\Runner\Model\Loader\Processor;

class PrototypeExpandingProcessor implements Processor
{
    const KEY_PROTOTYPES = 'prototypes';
    const KEY_NODES = 'nodes';
    const KEY_PROTOTYPE = 'prototype';


    public function process(array $manifest): array
    {
        $prototypes = [];
        if (isset($manifest[self::KEY_PROTOTYPES])) {
            $prototypes = $manifest[self::KEY_PROTOTYPES];
            unset($manifest[self::KEY_PROTOTYPES]);
        }

        if (!isset($manifest[self::KEY_NODES])) {
            return $manifest;
        }

        foreach ($manifest[self::KEY_NODES] as $packageName => &$package) {
            if (!isset($package[self::KEY_PROTOTYPE])) {
                continue;
            }

            if (!isset($prototypes[$package[self::KEY_PROTOTYPE]])) {
                throw new PrototypeNotFound(sprintf(
                    'Prototype "%s" for package "%s" not found, known prototypes: "%s"',
                    $package[self::KEY_PROTOTYPE],
                    $packageName,
                    implode('", "', array_keys($prototypes))
                ));
            }

            $package = array_merge_recursive($prototypes[$package[self::KEY_PROTOTYPE]], $package);
            unset($package[self::KEY_PROTOTYPE]);
        }

        return $manifest;
    }
}
