<?php

namespace Maestro\Model\Console;

interface Console
{
    public function id(): string;

    public function write(string $bytes): void;
}
