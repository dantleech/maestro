<?php

namespace Maestro\Extension\Runner\Command\Behavior;

use Amp\Loop;
use Maestro\Extension\Runner\Loader\GraphConstructor;
use Maestro\Extension\Runner\Loader\ManifestLoader;
use Maestro\Library\Graph\GraphTaskScheduler;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\State;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Worker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GraphBehavior
{
    private const ARG_MANIFEST = 'manifest';

    private const POLL_TIME_DISPATCH = 10;
    private const POLL_TIME_RENDER = 100;

    /**
     * @var ManifestLoader
     */
    private $loader;

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

    public function __construct(
        ManifestLoader $loader,
        GraphConstructor $constructor,
        GraphTaskScheduler $scheduler,
        Worker $worker,
        LoggerInterface $logger,
        Queue $queue
    ) {
        $this->loader = $loader;
        $this->constructor = $constructor;
        $this->scheduler = $scheduler;
        $this->worker = $worker;
        $this->logger = $logger;
        $this->queue = $queue;
    }

    public function configure(Command $command): void
    {
    }

    public function loadGraph(InputInterface $input): Graph
    {
        return $this->constructor->construct(
            $this->loader->load()
        );
    }

    public function run(InputInterface $input, OutputInterface $output, Graph $graph)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        Loop::repeat(self::POLL_TIME_DISPATCH, function () use ($graph) {
            static $started = false;
            $this->scheduler->run($graph);
            $this->worker->start();


            if ($graph->nodes()->byStates(State::CANCELLED(), State::FAILED(), State::DONE())->count() === $graph->nodes()->count()) {
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
                State::DONE()
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
                $nodes->byState(State::DONE())->count(),
            ));
        });

        Loop::run();
    }
}
