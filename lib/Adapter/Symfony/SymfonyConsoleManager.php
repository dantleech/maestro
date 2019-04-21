<?php

namespace Maestro\Adapter\Symfony;

use Maestro\Model\Console\Exception\ConsoleNotFound;
use Maestro\Model\Console\Console;
use Maestro\Model\Console\ConsoleManager;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Maestro\Adapter\Symfony\SymfonyConsole;

class SymfonyConsoleManager implements ConsoleManager
{
    /**
     * @var ConsoleOutputInterface
     */
    private $output;

    /**
     * @var Console[]
     */
    private $consoles = [];

    public function __construct(ConsoleOutputInterface $output)
    {
        $this->output = $output;
    }

    public function new(string $id = null): Console
    {
        $id = $id ?: uniqid();
        $this->consoles[$id] = new SymfonyConsole($id, $this->output);
        return $this->consoles[$id];
    }

    public function stdout(string $id): Console
    {
        return $this->get($id);
    }

    public function stderr(string $id): Console
    {
        return $this->get($id);
    }

    private function get(string $id): Console
    {
        if (!isset($this->consoles[$id])) {
            throw new ConsoleNotFound(sprintf(
                'Console "%s" was not found, known consoles "%s"',
                $id, implode('", "', array_keys($this->consoles))
            ));
        }

        return $this->consoles[$id];
    }
}
