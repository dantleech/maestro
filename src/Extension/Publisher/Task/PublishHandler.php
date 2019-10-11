<?php

namespace Maestro\Extension\Publisher\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Task\Artifacts;
use Webmozart\PathUtil\Path;

class PublishHandler
{
    /**
     * @var string
     */
    private $artifactsDirectory;

    public function __construct(string $artifactsDirectory)
    {
        $this->artifactsDirectory = $artifactsDirectory;
    }

    public function __invoke(PublishTask $task, Package $package, Artifacts $artifacts): Promise
    {
        $filename = $this->resolveFilename($package);

        echo(json_encode([
            'package' => $package->name(),
            'artifacts' => $this->serializeArtifacts($artifacts)
        ], JSON_PRETTY_PRINT));

        return new Success([]);
    }

    private function resolveFilename(Package $package)
    {
        return Path::join([
            $this->artifactsDirectory,
            str_replace('/', '-', $package->name()),
            '.json'
        ]);
    }

    private function serializeArtifacts(Artifacts $artifacts): array
    {
        return array_reduce($artifacts->toArray(), function (array $serialized, object $artifact) {
            $class = str_replace('\\', '.', get_class($artifact));
            $serialized[$class] = $artifact;
            return $serialized;
        }, []);
    }
}
