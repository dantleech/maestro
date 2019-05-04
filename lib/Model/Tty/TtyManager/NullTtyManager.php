<?php

namespace Maestro\Model\Tty\TtyManager;

use Maestro\Model\Tty\Tty;
use Maestro\Model\Tty\TtyManager;

class NullTtyManager implements TtyManager
{
    public function stdout(string $id): Tty
    {
        return new NullTty();
    }

    public function stderr(string $id): Tty
    {
        return new NullTty();
    }
}
