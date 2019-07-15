<?php

namespace Maestro\Loader;

use Maestro\Util\Cast;
use RuntimeException;
use Webmozart\PathUtil\Path;
use function Safe\json_decode;

class ManifestLoader
{
    /**
     * @var Processor[]
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

        return json_decode(Cast::toString(file_get_contents($path)), true);
    }

    private function resolvePath(string $planPath)
    {
        if (Path::isAbsolute($planPath)) {
            return $planPath;
        }

        return Path::join($this->workingDirectory, $planPath);
    }
}
