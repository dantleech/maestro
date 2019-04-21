<?php

namespace Maestro\Model\Console;

interface Console
{
    public function write(string $bytes): void;

    public function writeln(string $line): void;
}
