<?php

namespace Maestro\Adapter\Symfony;

use Maestro\Model\Console\Console;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyConsole implements Console
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $role;

    /**
     * @var string
     */
    private $color;

    public function __construct(string $name, OutputInterface $output, string $role, string $color)
    {
        $this->output = $output;
        $this->name = $name;
        $this->role = $role;
        $this->color = $color;
    }

    public function write(string $bytes): void
    {
        $this->output->write($this->decorate($bytes));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function id(): string
    {
        return $this->name;
    }

    public function writeln(string $line): void
    {
        $this->output->writeln($this->decorate($line, $this->role));
    }

    private function decorate(string $line, ?string $role = null)
    {
        return sprintf(
            '<fg=%s>%s%s</>',
            $this->color,
            $role ? $role . ': ' : '',
            $line
        );
    }
}
