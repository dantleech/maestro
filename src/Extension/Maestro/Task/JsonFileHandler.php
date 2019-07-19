<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Task;
use Maestro\Node\Environment;
use Maestro\Node\TaskHandler;
use Maestro\Workspace\Workspace;
use function Safe\json_encode;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\file_get_contents;

class JsonFileHandler implements TaskHandler
{
    public function execute(Task $task, Environment $environment): Promise
    {
        assert($task instanceof JsonFileTask);
        $manifestDir = $environment->get('manifest.dir');
        assert(is_string($manifestDir));
        $workspace = $environment->get('workspace');
        assert($workspace instanceof Workspace);
        $existingData = [];

        if (file_exists($workspace->absolutePath($task->targetPath()))) {
            $existingData = json_decode(
                file_get_contents($workspace->absolutePath($task->targetPath())),
                true
            );
        }

        if ($task->merge()) {
            file_put_contents(
                $workspace->absolutePath($task->targetPath()),
                json_encode(
                    array_replace_recursive($existingData, $task->merge()),
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
                ),
            );
        }

        return new Success($environment);
    }
}
