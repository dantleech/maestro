<?php

namespace Maestro\Model\Console\ConsoleManager;

use Maestro\Model\Console\Console;

class NullConsole implements Console
{
    public function write(string $bytes): void
    {
    }

    public function writeln(string $line): void
    {
    }
}
