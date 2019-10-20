<?php

namespace Maestro\Extension\File\Task;

use Amp\Parallel\Worker;
use Amp\Promise;
use Maestro\Extension\File\Amp\Task\PurgeDirectoryAmpTask;
use Maestro\Extension\File\Task\Exception\CouldNotPurgeDirectory;
use Webmozart\PathUtil\Path;

class PurgeDirectoryHandler
{
    /**
     * @var string
     */
    private $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = Path::canonicalize($rootPath);
    }

    public function __invoke(PurgeDirectoryTask $task): Promise
    {
        $path = Path::canonicalize($task->path());

        if (!Path::isAbsolute($path)) {
            throw new CouldNotPurgeDirectory(sprintf(
                'Path must be absolute got "%s"',
                $path
            ));
        }

        if (false === strpos($path, $this->rootPath)) {
            throw new CouldNotPurgeDirectory(sprintf(
                'Trying to purge directory "%s" outside of root path "%s"',
                $path,
                $this->rootPath
            ));
        }

        return \Amp\call(function () use ($path) {
            yield Worker\enqueue(new PurgeDirectoryAmpTask($path));
            return [];
        });
    }
}
