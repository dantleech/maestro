<?php

namespace Maestro\Extension\Runner\Command\Behavior;

use Amp\Loop;
use Maestro\Extension\Runner\Model\TagParser;
use Maestro\Extension\Runner\Model\Loader\GraphConstructor;
use Maestro\Library\Graph\GraphTaskScheduler;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\State;
use Maestro\Library\Report\Report;
use Maestro\Library\Report\ReportRegistry;
use Maestro\Library\Task\Queue;
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
    private const OPTION_TAGS = 'tags';
    private const OPTION_REPORT = 'report';

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

    public function __construct(
        GraphConstructor $constructor,
        GraphTaskScheduler $scheduler,
        Worker $worker,
        LoggerInterface $logger,
        Queue $queue,
        TagParser $tagParser,
        ReportRegistry $reportRegistry
    ) {
        $this->constructor = $constructor;
        $this->scheduler = $scheduler;
        $this->worker = $worker;
        $this->logger = $logger;
        $this->queue = $queue;
        $this->tagParser = $tagParser;
        $this->reportRegistry = $reportRegistry;
    }

    public function configure(Command $command): void
    {
        $command->addOption(self::OPTION_TAGS, 't', InputOption::VALUE_REQUIRED, 'Comma separated list of tags');
        $command->addOption(
            self::OPTION_REPORT,
            'r',
            InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
            'Reports to render',
            ['run']
        );
    }

    public function loadGraph(InputInterface $input): Graph
    {
        $graph = $this->constructor->construct();

        $tags = $input->getOption(self::OPTION_TAGS);
        if ($tags) {
            $tags = $this->tagParser->parse(Cast::toString($tags));
            $this->logger->notice(sprintf('Pruning graph for tags: "%s"', implode('", "', $tags)));
            $graph = $graph->pruneForTags(
                ...$tags
            );
        }

        return $graph;
    }

    public function run(InputInterface $input, OutputInterface $output, Graph $graph)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

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
        });

        Loop::run();
    }

    /**
     * @return Report[]
     */
    public function fetchReports(InputInterface $input): array
    {
        $reports = Cast::toArray($input->getOption(self::OPTION_REPORT));
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
