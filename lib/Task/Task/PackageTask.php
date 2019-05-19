<?php

namespace Maestro\Task\Task;

use Maestro\Task\Task;

class PackageTask implements Task
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function handler(): string
    {
    }

    public function name(): string
    {
        return $this->name;
    }
}
