<?php

namespace Phpactor\Extension\Maestro\Console\Logger;

use Phpactor\Extension\Maestro\Model\Logger;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger implements Logger
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function write(string $message): void
    {
        $this->output->writeln($message);
    }
}
