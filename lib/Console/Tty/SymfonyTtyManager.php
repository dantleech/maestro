<?php

namespace Maestro\Console\Tty;

use Maestro\Model\Tty\Tty;
use Maestro\Model\Tty\TtyManager;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyTtyManager implements TtyManager
{
    private $output;

    /**
     * @var Tty[]
     */
    private $consoles = [];

    private $assignedColors = [];

    private $colors = [
        'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'
    ];

    private $colorIndex = 0;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function stdout(string $id): Tty
    {
        return $this->get($id, 'stdout');
    }

    public function stderr(string $id): Tty
    {
        return $this->get($id, 'stderr');
    }

    private function get(string $id, string $role): Tty
    {
        $identifier = $id.$role;

        if (isset($this->consoles[$identifier])) {
            return $this->consoles[$identifier];
        }

        if (!isset($this->assignedColors[$id])) {
            $this->assignedColors[$id] = $this->colors[$this->colorIndex++ %count($this->colors)];
        }

        $this->consoles[$identifier] = new SymfonyTty($id, $this->output, $id, $this->assignedColors[$id]);

        return $this->consoles[$identifier];
    }
}
