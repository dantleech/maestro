<?php

namespace Maestro\Model\Console;

interface ConsoleManager
{
    public function new(): Console;

    public function get(string $id): Console;
}
