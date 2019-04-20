<?php

namespace Maestro\Console;

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

    public function __construct(string $name, OutputInterface $output)
    {
        $this->output = $output;
        $this->name = $name;
    }

    public function write(string $bytes): void
    {
        $this->output->write($bytes);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function id(): string
    {
        return $this->name;
    }
}
