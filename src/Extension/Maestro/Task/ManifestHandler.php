<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Environment;
use Maestro\Node\Task;
use Maestro\Node\TaskHandler;
use Webmozart\PathUtil\Path;

class ManifestHandler implements TaskHandler
{
    public function execute(Task $manifest, Environment $environment): Promise
    {
        assert($manifest instanceof ManifestTask);
        $manifestPath = $manifest->path();

        return new Success($environment->builder()->withParameters(array_merge($manifest->environment(), [
            'manifest.path' => $manifestPath,
            'manifest.dir' => $manifestPath ? Path::getDirectory($manifestPath) : null,
        ]))->build());
    }
}
