<?php

namespace Maestro\Model\Tty;

interface TtyManager
{
    public function stdout(string $id): Tty;

    public function stderr(string $id): Tty;
}
