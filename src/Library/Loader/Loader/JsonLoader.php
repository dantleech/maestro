<?php

namespace Maestro\Library\Loader\Loader;

use JsonException;
use Maestro\Library\Loader\Exception\CouldNotDecode;
use Maestro\Library\Loader\Loader;
use Maestro\Library\Util\Cast;
use RuntimeException;

class JsonLoader implements Loader
{
    public function load(string $resource): array
    {
        if (!file_exists($resource)) {
            throw new RuntimeException(sprintf(
                'Plan file "%s" does not exist',
                $resource
            ));
        }

        try {
            $array = json_decode(
                Cast::toString(file_get_contents($resource)),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $jsonException) {
            throw new CouldNotDecode(sprintf(
                'Could not JSON decode file "%s": "%s"',
                $resource,
                $jsonException->getMessage()
            ));
        }

        return $array;
    }
}
