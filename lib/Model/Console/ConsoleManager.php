<?php

namespace Maestro\Model\Console;

interface ConsoleManager
{
    public function stdout(string $id): Console;

    public function stderr(string $id): Console;
}
