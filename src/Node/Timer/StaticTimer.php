<?php

namespace Maestro\Node\Timer;

use Maestro\Node\Timer;

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
