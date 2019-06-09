<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\TaskHandler;

class ManifestHandler implements TaskHandler
{
    public function __invoke(ManifestTask $manifest, Artifacts $artifacts): Promise
    {
        return new Success(Artifacts::create([
            'manifest_path' => $manifest->path(),
            'manifest_dir' => dirname($manifest->path())
        ]));
    }
}
