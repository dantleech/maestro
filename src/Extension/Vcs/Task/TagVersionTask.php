<?php

namespace Maestro\Extension\Vcs\Task;

use Maestro\Library\Task\Task;

class TagVersionTask implements Task
{
    /**
     * @var string|null
     */
    private $tag;

    public function __construct(string $tag = null)
    {
        $this->tag = $tag;
    }

    public function description(): string
    {
        return 'applying tag';
    }

    public function tag(): ?string
    {
        return $this->tag;
    }
}
