<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Webmozart\PathUtil\Path;

class ManifestHandler implements TaskHandler
{
    public function execute(Task $manifest, Environment $environment): Promise
    {
        assert($manifest instanceof ManifestTask);
        $manifestPath = $manifest->path();

        $builder = $environment->builder();
        $builder->withVars(array_merge($manifest->vars(), [
            'manifest.path' => $manifestPath,
            'manifest.dir' => $manifestPath ? Path::getDirectory($manifestPath) : null,
        ]));
        $builder->mergeEnv($manifest->env());

        return new Success($builder->build());
    }
}
