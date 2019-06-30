<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\TaskHandler;
use Maestro\Workspace\Workspace;
use function Safe\json_encode;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\file_get_contents;

class JsonFileHandler implements TaskHandler
{
    public function __invoke(JsonFileTask $task, Artifacts $artifacts)
    {
        $manifestDir = $artifacts->get('manifest.dir');
        assert(is_string($manifestDir));
        $workspace = $artifacts->get('workspace');
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
                    JSON_PRETTY_PRINT
                ),
            );
        }

        return new Success();
    }
}
