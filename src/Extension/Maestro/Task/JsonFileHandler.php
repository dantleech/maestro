<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Success;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskHandler;
use Maestro\Workspace\Workspace;
use function Safe\json_encode;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\file_get_contents;

class JsonFileHandler implements TaskHandler
{
    public function __invoke(JsonFileTask $task, TaskContext $context)
    {
        $manifestDir = $context->artifacts()->get('manifest.dir');
        assert(is_string($manifestDir));
        $workspace = $context->artifacts()->get('workspace');
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

        return new Success();
    }
}
