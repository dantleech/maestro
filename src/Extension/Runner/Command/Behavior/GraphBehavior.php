<?php

namespace Maestro\Extension\Runner\Command\Behavior;

use Amp\Loop;
use Maestro\Extension\Runner\Loader\GraphConstructor;
use Maestro\Extension\Runner\Loader\ManifestLoader;
use Maestro\Library\Graph\Graph;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

    public function __construct(ManifestLoader $loader, GraphConstructor $constructor)
    {
        $this->loader = $loader;
        $this->constructor = $constructor;
    }

    public function configure(Command $command): void
    {
        $command->addArgument(self::ARG_MANIFEST, InputArgument::REQUIRED, 'Path to the plan to execute');
    }

    public function loadGraph(InputInterface $input): Graph
    {
        return $this->constructor->construct(
            $this->loader->load(
                $input->getArgument(self::ARG_MANIFEST)
            )
        );
    }

    public function run(InputInterface $input, OutputInterface $output, Graph $graph)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        Loop::run();
    }
}
