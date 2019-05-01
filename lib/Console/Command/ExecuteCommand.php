<?php

namespace Maestro\Console\Command;

use Maestro\Console\Report\QueueReport;
use Maestro\Console\Util\Cast;
use Maestro\Service\CommandRunner;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends Command
{
    const ARG_COMMAND = 'exec';
    const OPTION_RESET = 'reset';
    const OPTION_QUERY = 'query';

    /**
     * @var CommandRunner
     */
    private $commandRunner;

    /**
     * @var QueueReport
     */
    private $report;

    public function __construct(CommandRunner $commandRunner, QueueReport $report)
    {
        parent::__construct();
        $this->commandRunner = $commandRunner;
        $this->report = $report;
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_COMMAND, InputArgument::REQUIRED, 'Command to run on repositories');
        $this->addOption(self::OPTION_QUERY, 't', InputOption::VALUE_REQUIRED, 'Query packages (wildcard * is permitted)', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statuses = $this->commandRunner->run(
            Cast::toString($input->getArgument(self::ARG_COMMAND)),
            Cast::toString($input->getOption(self::OPTION_QUERY))
        );

        $this->report->render($output, $statuses);
    }
}
