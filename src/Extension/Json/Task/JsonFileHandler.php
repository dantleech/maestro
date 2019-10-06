<?php

namespace Maestro\Extension\Json\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Workspace\Workspace;
use function Safe\json_encode;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\file_get_contents;

class JsonFileHandler
{
    public function __invoke(
        JsonFileTask $task,
        Workspace $workspace
    ): Promise {
        $existingData = [];

        if (file_exists($workspace->absolutePath($task->targetPath()))) {
            $existingData = json_decode(
                file_get_contents($workspace->absolutePath($task->targetPath())),
                true
            );
        }

        if ($task->data()) {
            file_put_contents(
                $workspace->absolutePath($task->targetPath()),
                json_encode(
                    array_replace_recursive($existingData, $task->data()),
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
                ),
            );
        }

        return new Success([]);
    }
}
