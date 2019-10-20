<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Maestro\Extension\Runner\Task\InitTask;
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
    private $manifestPath;

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(string $workingDirectory, string $manifestPath, array $processors)
    {
        $this->processors = $processors;
        $this->manifestPath = $manifestPath;
        $this->workingDirectory = $workingDirectory;
    }

    public function load(): ManifestNode
    {
        $path = $this->resolvePath();
        $data = $this->loadManifestArray($path);

        foreach ($this->processors as $processor) {
            $data = $processor->process($data);
        }

        return ManifestNode::fromArray(array_merge($data, [
            'name' => '',
            'type' => InitTask::class,
            'args' => array_merge($data['args'] ?? [], [
                'path' => $path
            ]),
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

    private function resolvePath(): string
    {
        $planPath = $this->manifestPath;
        if (Path::isAbsolute($planPath)) {
            return $planPath;
        }

        return Path::join($this->workingDirectory, $planPath);
    }
}
