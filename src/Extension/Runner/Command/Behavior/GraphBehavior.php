<?php

namespace Maestro\Extension\Runner\Command\Behavior;

use Amp\Loop;
use Maestro\Extension\Runner\Model\GraphFilter;
use Maestro\Extension\Runner\Model\TagParser;
use Maestro\Extension\Runner\Model\Loader\GraphConstructor;
use Maestro\Library\Graph\GraphTaskScheduler;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\State;
use Maestro\Library\Report\Report;
use Maestro\Library\Report\ReportRegistry;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\Worker;
use Maestro\Library\Util\Cast;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GraphBehavior
{
    private const POLL_TIME_DISPATCH = 10;
    private const POLL_TIME_RENDER = 100;
    private const OPT_REPORT = 'report';
    private const OPT_NO_LOOP = 'no-loop';
    private const OPT_FILTER = 'filter';

    /**
     * @var GraphConstructor
     */
    private $constructor;

    /**
     * @var GraphTaskScheduler
     */
    private $scheduler;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var TagParser
     */
    private $tagParser;

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var GraphFilter
     */
    private $filter;

    public function __construct(
        GraphConstructor $constructor,
        GraphTaskScheduler $scheduler,
        Worker $worker,
        LoggerInterface $logger,
        Queue $queue,
        TagParser $tagParser,
        ReportRegistry $reportRegistry,
        GraphFilter $filter
    ) {
        $this->constructor = $constructor;
        $this->scheduler = $scheduler;
        $this->worker = $worker;
        $this->logger = $logger;
        $this->queue = $queue;
        $this->tagParser = $tagParser;
        $this->reportRegistry = $reportRegistry;
        $this->filter = $filter;
    }

    public function configure(Command $command): void
    {
        $command->addOption(self::OPT_NO_LOOP, null, InputOption::VALUE_NONE, 'Do not run the event loop');
        $command->addOption(self::OPT_FILTER, null, InputOption::VALUE_REQUIRED, 'Filter');
        $command->addOption(
            self::OPT_REPORT,
            'r',
            InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
            'Reports to render',
            ['run']
        );
    }

    public function loadGraph(InputInterface $input): Graph
    {
        $graph = $this->constructor->construct();

        $filter = Cast::toStringOrNull($input->getOption(self::OPT_FILTER));
        if (null !== $filter) {
            $this->logger->notice(sprintf('Pruning graph to filter expression: "%s"', $filter));
            $graph = $this->filter->filter($graph, $filter);
        }

        return $graph;
    }

    public function run(InputInterface $input, OutputInterface $output, Graph $graph)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        if ($input->getOption(self::OPT_NO_LOOP)) {
            return;
        }

        Loop::repeat(self::POLL_TIME_DISPATCH, function () use ($graph) {
            static $started = false;
            $this->scheduler->run($graph);
            $this->worker->start();


            if ($graph->nodes()->byStates(State::CANCELLED(), State::FAILED(), State::SUCCEEDED())->count() === $graph->nodes()->count()) {
                Loop::stop();
            }
        });

        Loop::onSignal(SIGINT, function () {
            $this->logger->notice('SIGINT received, shutting down');
            Loop::stop();
        });

        Loop::repeat(1000, function () use ($graph) {
            $nodes = $graph->nodes();

            $completed = $nodes->byStates(
                State::CANCELLED(),
                State::FAILED(),
                State::SUCCEEDED()
            )->count();

            $this->logger->notice(sprintf(
                '%s%% %s/%s: %s pending, %s queued, %s busy, %s failed, %s cancelled, %s done',
                number_format(($completed / $nodes->count()) * 100),
                $completed,
                $nodes->count(),
                $nodes->byState(State::IDLE())->count(),
                $this->queue->count(),
                $this->worker->processingJobCount(),
                $nodes->byState(State::FAILED())->count(),
                $nodes->byState(State::CANCELLED())->count(),
                $nodes->byState(State::SUCCEEDED())->count(),
            ));
            $this->logger->debug(sprintf(
                'Currently %s',
                implode(',', array_map(function (Task $task) {
                    return $task->description();
                }, $this->worker->processingTasks()))
            ));
        });

        Loop::run();
    }

    /**
     * @return Report[]
     */
    public function fetchReports(InputInterface $input): array
    {
        $reports = Cast::toArray($input->getOption(self::OPT_REPORT));
        return array_values((array)array_combine(array_map('ucfirst', $reports), array_map(function (string $reportName) {
            return $this->reportRegistry->get($reportName);
        }, $reports)));
    }

    public function renderReports(Graph $graph, Report ...$reports)
    {
        return array_map(function (Report $report) use ($graph) {
            $report->render($graph);
        }, $reports);
    }
}
