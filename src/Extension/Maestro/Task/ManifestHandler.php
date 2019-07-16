<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Artifacts;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskHandler;
use Webmozart\PathUtil\Path;

class ManifestHandler implements TaskHandler
{
    public function __invoke(ManifestTask $manifest, TaskContext $context): Promise
    {
        $manifestPath = $manifest->path();

        return new Success(Artifacts::create(array_merge($manifest->context(), [
            'manifest.path' => $manifestPath,
            'manifest.dir' => $manifestPath ? Path::getDirectory($manifestPath) : null,
        ])));
    }
}
