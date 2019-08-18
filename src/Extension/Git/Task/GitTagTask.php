<?php

namespace Maestro\Extension\Git\Task;

use Maestro\Graph\Task;

class GitTagTask implements Task
{
    /**
     * @var string
     */
    private $tagName;

    public function __construct(string $tagName)
    {
        $this->tagName = $tagName;
    }

    public function description(): string
    {
        return sprintf('applying tag "%s"', $this->tagName);
    }

    public function tagName(): string
    {
        return $this->tagName;
    }
}
