<?php

namespace Maestro\Console\Command;

use Amp\Loop;
use Maestro\Console\Progress\Progress;
use Maestro\Console\Progress\ProgressRegistry;
use Maestro\Console\Report\QueueReport;
use Maestro\Console\Util\Cast;
use Maestro\Service\Applicator;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplyCommand extends Command
{
    const ARG_COMMAND = 'update';
    const OPTION_QUERY = 'query';
    const ARG_TARGET = 'target';

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
        $this->addArgument(self::ARG_TARGET, InputArgument::OPTIONAL, 'Target');
        $this->addOption(self::OPTION_QUERY, 'f', InputOption::VALUE_REQUIRED, 'Query packages (wildcard * is permitted)', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        assert($output instanceof ConsoleOutputInterface);
        $progress = $this->registry->get(Cast::toString($input->getOption('progress')));

        $progressOutput = $output->section();
        $this->renderProgress($progress, $progressOutput);
        Loop::repeat(500, function () use ($progress, $progressOutput) {
            $this->renderProgress($progress, $progressOutput);
        });

        $statuses = $this->applicator->apply(
            Cast::toString($input->getOption(self::OPTION_QUERY)),
            Cast::toStringOrNull($input->getArgument(self::ARG_TARGET))
        );

        $this->renderProgress($progress, $progressOutput);
        $this->report->render($statuses);
    }






    private function renderProgress(Progress $progress, $progressOutput)
    {
        $rendered = $progress->render();

        if (null !== $rendered) {
            $progressOutput->overwrite($progress->render());
        }
    }
}
