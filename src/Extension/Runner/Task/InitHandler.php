<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Success;
use Maestro\Extension\Runner\Model\Loader\Manifest;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\ManifestPath;
use Maestro\Library\Support\Variables\Variables;

class InitHandler
{
    /**
     * @var Manifest
     */
    private $manifest;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    public function __invoke(InitTask $task)
    {
        $artifacts = [
            new Environment($this->manifest->env()),
            new Variables($this->manifest->vars()),
        ];

        $path = $this->manifest->path();

        if ($path) {
            $artifacts[] = new ManifestPath($path);
        }

        return new Success($artifacts);
    }
}
