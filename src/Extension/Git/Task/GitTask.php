<?php

namespace Maestro\Extension\Git\Task;

use Maestro\Graph\Task;

class GitTask implements Task
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $reset;

    /**
     * @var bool
     */
    private $update;

    public function __construct(string $url, bool $reset = false, bool $update = false)
    {
        $this->url = $url;
        $this->reset = $reset;
        $this->update = $update;
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
