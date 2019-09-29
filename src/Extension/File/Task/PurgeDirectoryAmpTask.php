<?php

namespace Maestro\Extension\File\Task;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;
use Symfony\Component\Filesystem\Filesystem;

class PurgeDirectoryAmpTask implements Task
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Environment $environment)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->path);
    }
}
