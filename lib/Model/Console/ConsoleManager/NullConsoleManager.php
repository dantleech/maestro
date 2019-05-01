<?php

namespace Maestro\Model\Console\ConsoleManager;

use Maestro\Model\Console\Console;
use Maestro\Model\Console\ConsoleManager;

class NullConsoleManager implements ConsoleManager
{
    public function stdout(string $id): Console
    {
        return new NullConsole($id);
    }

    public function stderr(string $id): Console
    {
        return new NullConsole($id);
    }
}
