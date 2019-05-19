<?php

namespace Maestro\Extension\Maestro\Command;

use Amp\Loop;
use Maestro\Dumper\GraphRenderer;
use Maestro\Loader\Manifest;
use Maestro\RunnerBuilder;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class RunCommand extends Command
{
    const ARG_PLAN = 'plan';

    /**
     * @var RunnerBuilder
     */
    private $builder;

    public function __construct(RunnerBuilder $builder)
    {
        parent::__construct();
        $this->builder = $builder;
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_PLAN, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        $runner = $this->builder->build();

        $graph = $runner->run(Manifest::loadFromArray($this->loadManifestArray($input->getArgument(self::ARG_PLAN))));

        Loop::repeat(100, function () use ($graph, $section) {
            $section->overwrite((new GraphRenderer())->render($graph));
        });

        Loop::run();
    }

    private function loadManifestArray(string $planPath)
    {
        $path = $this->resolvePath($planPath);
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Plan file "%s" does not exist', $path
            ));
        }

        $array = json_decode(file_get_contents($planPath), true);

        if (json_last_error()) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: "%s"', json_last_error_msg()
            ));
        }

        return $array;

    }

    private function resolvePath(string $planPath)
    {
        if (Path::isAbsolute($planPath)) {
            return $planPath;
        }

        return Path::join(getcwd(), $planPath);
    }
}
