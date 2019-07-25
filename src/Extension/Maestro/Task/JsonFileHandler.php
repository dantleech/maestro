<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Task;
use Maestro\Graph\Environment;
use Maestro\Graph\TaskHandler;
use function Safe\json_encode;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\file_get_contents;

class JsonFileHandler implements TaskHandler
{
    public function execute(Task $task, Environment $environment): Promise
    {
        assert($task instanceof JsonFileTask);
        $manifestDir = $environment->vars()->get('manifest.dir');
        assert(is_string($manifestDir));
        $workspace = $environment->workspace();
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
