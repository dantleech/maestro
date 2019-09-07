<?php

namespace Maestro\Application\Loader\Processor;

use Maestro\Application\Loader\Exception\PrototypeNotFound;
use Maestro\Application\Loader\Processor;

class PrototypeExpandingProcessor implements Processor
{
    const KEY_PROTOTYPES = 'prototypes';
    const KEY_PACKAGES = 'packages';
    const KEY_PROTOTYPE = 'prototype';


    public function process(array $manifest): array
    {
        $prototypes = [];
        if (isset($manifest[self::KEY_PROTOTYPES])) {
            $prototypes = $manifest[self::KEY_PROTOTYPES];
            unset($manifest[self::KEY_PROTOTYPES]);
        }

        if (!isset($manifest[self::KEY_PACKAGES])) {
            return $manifest;
        }

        foreach ($manifest[self::KEY_PACKAGES] as $packageName => &$package) {
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
