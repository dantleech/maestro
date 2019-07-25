<?php

namespace Maestro\Graph\Timer;

use Maestro\Graph\Timer;

class StaticTimer implements Timer
{
    /**
     * @var int
     */
    private $elapsedSeconds;

    private function __construct(int $elapsedSeconds)
    {
        $this->elapsedSeconds = $elapsedSeconds;
    }

    public function reset(): void
    {
    }

    public function elapsed(): int
    {
        return $this->elapsedSeconds;
    }

    public static function hasElapsed(int $int)
    {
        return new self($int);
    }
}
