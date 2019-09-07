<?php

namespace Maestro\Application\Loader;

use Maestro\Library\Util\Cast;
use RuntimeException;
use Webmozart\PathUtil\Path;

class ManifestLoader
{
    /**
     * @var array
     */
    private $processors = [];

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(string $workingDirectory, array $processors)
    {
        $this->processors = $processors;
        $this->workingDirectory = $workingDirectory;
    }

    public function load(string $path)
    {
        $path = $this->resolvePath($path);
        $data = $this->loadManifestArray($path);

        foreach ($this->processors as $processor) {
            $data = $processor->process($data);
        }

        return Manifest::loadFromArray(array_merge($data, [
            'path' => $path
        ]));
    }

    private function loadManifestArray(string $path)
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Plan file "%s" does not exist',
                $path
            ));
        }

        $array = json_decode(Cast::toString(file_get_contents($path)), true);

        if (false === $array) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: "%s"',
                json_last_error_msg()
            ));
        }

        return $array;
    }

    private function resolvePath(string $planPath)
    {
        if (Path::isAbsolute($planPath)) {
            return $planPath;
        }

        return Path::join($this->workingDirectory, $planPath);
    }
}
