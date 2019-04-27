<?php

namespace Maestro\Console\Command;

use Maestro\Console\Report\QueueReport;
use Maestro\Model\Job\QueueStatus;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Service\Applicator;
use Maestro\Service\CommandRunner;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApplyCommand extends Command
{
    const ARG_COMMAND = 'update';
    const OPTION_RESET = 'reset';
    const OPTION_QUERY = 'query';

    /**
     * @var Applicator
     */
    private $applicator;

    /**
     * @var QueueReport
     */
    private $report;

    public function __construct(Applicator $applicator, QueueReport $report)
    {
        parent::__construct();
        $this->applicator = $applicator;
        $this->report = $report;
    }

    protected function configure()
    {
        $this->addOption(self::OPTION_RESET, null, InputOption::VALUE_NONE, 'Reset the package repositories');
        $this->addOption(self::OPTION_QUERY, 't', InputOption::VALUE_REQUIRED, 'Query packages (wildcard * is permitted)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statuses = $this->applicator->apply(
            (bool) $input->getOption(self::OPTION_RESET),
            (string) $input->getOption(self::OPTION_QUERY)
        );

        $this->report->render($output, $statuses);
    }
}
