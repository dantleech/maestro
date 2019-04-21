<?php

namespace Maestro\Model\Console;

interface ConsoleManager
{
    public function new(string $id = null): Console;

    public function stdout(string $id): Console;

    public function stderr(string $id): Console;
}
