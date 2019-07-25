<?php

namespace Maestro\Graph;

interface Timer
{
    public function reset(): void;

    public function elapsed(): int;
}
