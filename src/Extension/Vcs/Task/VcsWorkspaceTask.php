<?php

namespace Maestro\Extension\Vcs\Task;

use Maestro\Library\Task\Task;

class VcsWorkspaceTask implements Task
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $update;

    public function __construct(string $name, string $url, bool $update = true)
    {
        $this->name = $name;
        $this->url = $url;
        $this->update = $update;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function update(): bool
    {
        return $this->update;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function description(): string
    {
        return sprintf('creating VCS workspace for "%s"', $this->url);
    }
}
