<?php

namespace Maestro\Node\Timer;

use Maestro\Node\Timer;

class ClockTimer implements Timer
{
    private $start;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->start = time();
    }

    public function elapsed(): int
    {
        return time() - $this->start;
    }
}
