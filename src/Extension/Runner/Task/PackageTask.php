<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class PackageTask implements Task
{
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var string|null
     */
    private $version;

    public function __construct(
        ?string $name = null,
        ?string $version = null
    ) {
        $this->name = $name;
        $this->version = $version;
    }

    public function description(): string
    {
        return sprintf('sending package artifact %s downstream', $this->name);
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function version(): ?string
    {
        return $this->version;
    }
}
