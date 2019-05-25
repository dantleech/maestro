<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Task\Task;

class GitTask implements Task
{
    /**
     * @var string
     */
    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function description(): string
    {
        return sprintf('git clone "%s"', $this->url);
    }

    public function url(): string
    {
        return $this->url;
    }
}
