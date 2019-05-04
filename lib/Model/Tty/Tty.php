<?php

namespace Maestro\Model\Tty;

interface Tty
{
    public function write(string $bytes): void;

    public function writeln(string $line): void;
}
