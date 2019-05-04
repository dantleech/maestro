<?php

namespace Maestro\Model\Tty\TtyManager;

use Maestro\Model\Tty\Tty;

class NullTty implements Tty
{
    public function write(string $bytes): void
    {
    }

    public function writeln(string $line): void
    {
    }
}
