<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Maestro\Extension\Runner\Task\InitTask;
use Maestro\Library\Loader\Loader;
use Maestro\Library\Util\Cast;
use RuntimeException;
use Webmozart\PathUtil\Path;

class ManifestLoader
{
    /**
     * @var Processor[]
     */
    private $processors = [];

    /**
     * @var string
     */
    private $manifestPath;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var Loader
     */
    private $loader;

    public function __construct(Loader $loader, string $workingDirectory, array $processors)
    {
        $this->processors = $processors;
        $this->workingDirectory = $workingDirectory;
        $this->loader = $loader;
    }

    public function load(string $path): ManifestNode
    {
        $data = $this->loader->load($this->normalizePath($path));

        foreach ($this->processors as $processor) {
            $data = $processor->process($data);
        }


        return ManifestNode::fromArray(array_merge([
            'name' => '',
            'type' => InitTask::class,
            'args' => array_merge($data['args'] ?? [], [
                'path' => $path
            ]),
        ], $data));
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

    private function normalizePath(string $path): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        return Path::join($this->workingDirectory, $path);
    }
}
