<?php

namespace Maestro\Console\Command;

use Amp\Delayed;
use Amp\Loop;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Environment;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Unit\Parameters;
use Maestro\Service\CommandRunner;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class Exec extends Command
{
    const ARG_COMMAND = 'exec';

    /**
     * @var CommandRunner
     */
    private $commandRunner;

    public function __construct(CommandRunner $commandRunner)
    {
        parent::__construct();
        $this->commandRunner = $commandRunner;
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_COMMAND, InputArgument::REQUIRED, 'Command to run on repositories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandRunner->run($input->getArgument(self::ARG_COMMAND));
    }
}
