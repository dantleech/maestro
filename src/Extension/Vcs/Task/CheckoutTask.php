<?php

namespace Maestro\Extension\Vcs\Task;

use Maestro\Library\Task\Task;

class CheckoutTask implements Task
{
    /**
     * @var string
     */
    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function description(): string
    {
        return sprintf('checking out %s', $this->url);
    }
}
