<?php

namespace Maestro\Console\Command;

use Maestro\Console\Report\QueueReport;
use Maestro\Console\Util\Cast;
use Maestro\Service\Applicator;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApplyCommand extends Command
{
    const ARG_COMMAND = 'update';
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
        $this->addOption(self::OPTION_QUERY, 't', InputOption::VALUE_REQUIRED, 'Query packages (wildcard * is permitted)', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statuses = $this->applicator->apply(
            Cast::toString($input->getOption(self::OPTION_QUERY))
        );

        $this->report->render($output, $statuses);
    }
}
