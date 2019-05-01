<?php

namespace Maestro\Console;

use Maestro\Model\Console\Console;
use Maestro\Model\Console\ConsoleManager;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

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
    private $assignedColors = [];

    private $colors = [
        'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'
    ];

    private $colorIndex = 0;

    public function __construct(ConsoleOutputInterface $output)
    {
        $this->output = $output;
    }

    public function stdout(string $id): Console
    {
        return $this->get($id, 'stdout');
    }

    public function stderr(string $id): Console
    {
        return $this->get($id, 'stderr');
    }

    private function get(string $id, string $role): Console
    {
        $identifier = $id.$role;

        if (isset($this->consoles[$identifier])) {
            return $this->consoles[$identifier];
        }

        if (!isset($this->assignedColors[$id])) {
            $this->assignedColors[$id] = $this->colors[$this->colorIndex++ %count($this->colors)];
        }

        $this->consoles[$identifier] = new SymfonyConsole($id, $this->output, $id, $this->assignedColors[$id]);

        return $this->consoles[$identifier];
    }
}
