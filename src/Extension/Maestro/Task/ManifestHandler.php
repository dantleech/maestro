<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Artifacts;
use Maestro\Node\Task;
use Maestro\Node\TaskHandler;
use Webmozart\PathUtil\Path;

class ManifestHandler implements TaskHandler
{
    public function execute(Task $manifest, Artifacts $artifacts): Promise
    {
        assert($manifest instanceof ManifestTask);
        $manifestPath = $manifest->path();

        return new Success(Artifacts::create(array_merge($manifest->artifacts(), [
            'manifest.path' => $manifestPath,
            'manifest.dir' => $manifestPath ? Path::getDirectory($manifestPath) : null,
        ])));
    }
}
