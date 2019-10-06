<?php

namespace Maestro\Extension\Json\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Workspace\Workspace;
use RuntimeException;
use Safe\Exceptions\JsonException;
use function Safe\json_encode;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\file_get_contents;
use stdClass;

class JsonFileHandler
{
    public function __invoke(
        JsonFileTask $task,
        Workspace $workspace
    ): Promise {
        $existingData = new stdClass();

        if (file_exists($workspace->absolutePath($task->targetPath()))) {
            $jsonContents = file_get_contents($workspace->absolutePath($task->targetPath()));
            try {
                $existingData = json_decode(
                    $jsonContents,
                    false,
                    JSON_FORCE_OBJECT
                );
            } catch (JsonException $e) {
                throw new RuntimeException(sprintf(
                    'Could not parse JSON: "%s"',
                    $jsonContents
                ), 0, $e);
            }
        }

        if ($task->data()) {
            file_put_contents(
                $workspace->absolutePath($task->targetPath()),
                json_encode(
                    $this->mergeData($existingData, $task->data()),
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
                ),
            );
        }

        return new Success([]);
    }

    private function mergeData(object $existingData, $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($existingData, $key)) {
                $existingData->$key = [];
            }

            if (is_array($value) && is_object($existingData->$key)) {
                $this->mergeData($existingData->$key, $value);
                continue;
            }

            $existingData->$key = $value;
        }

        return $existingData;
    }
}
