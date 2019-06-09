<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\TaskHandler;
use Webmozart\PathUtil\Path;

class ManifestHandler implements TaskHandler
{
    public function __invoke(ManifestTask $manifest, Artifacts $artifacts): Promise
    {
        $manifestPath = $manifest->path();

        return new Success(Artifacts::create([
            'manifest.path' => $manifestPath,
            'manifest.dir' => $manifestPath ? Path::getDirectory($manifestPath) : null,
        ]));
    }
}
