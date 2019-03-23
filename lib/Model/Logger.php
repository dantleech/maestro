<?php

namespace Phpactor\Extension\Maestro\Model;

interface Logger
{
    public function write(string $message): void;
}
