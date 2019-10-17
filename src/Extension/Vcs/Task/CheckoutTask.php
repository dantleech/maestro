<?php

namespace Maestro\Extension\Vcs\Task;

use Maestro\Library\Task\Task;

class CheckoutTask implements Task
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $update;

    public function __construct(string $url, bool $update = true)
    {
        $this->url = $url;
        $this->update = $update;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function description(): string
    {
        return sprintf('checking out %s', $this->url);
    }

    public function update(): bool
    {
        return $this->update;
    }
}
