<?php

namespace Maestro\Node;

interface Timer
{
    public function reset(): void;

    public function elapsed(): int;
}
