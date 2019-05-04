<?php

namespace Maestro\Console\Command;

use Maestro\Console\Progress\ProgressRegistry;
use Maestro\Console\Report\QueueReport;
use Maestro\Console\Util\Cast;
use Maestro\Model\Job\Queues;
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

    /**
     * @var ProgressRegistry
     */
    private $registry;

    public function __construct(
        Applicator $applicator,
        QueueReport $report,
        ProgressRegistry $registry
    ) {
        parent::__construct();
        $this->applicator = $applicator;
        $this->report = $report;
        $this->registry = $registry;
    }

    protected function configure()
    {
        $this->addOption(self::OPTION_QUERY, 't', InputOption::VALUE_REQUIRED, 'Query packages (wildcard * is permitted)', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queues = Queues::create();
        $progress = $this->registry->get(Cast::toString($input->getOption('progress')));

        $statuses = $this->applicator->apply(
            $queues,
            Cast::toString($input->getOption(self::OPTION_QUERY))
        );

        $this->report->render($output, $statuses);
    }
}
