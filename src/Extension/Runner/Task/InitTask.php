<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Extension\Runner\Loader\Manifest;
use Maestro\Library\Task\Task;

class InitTask implements Task
{
    /**
     * @var Manifest
     */
    private $manifest;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    public function description(): string
    {
        return 'initializing';
    }

    public function manifest(): Manifest
    {
        return $this->manifest;
    }
}
