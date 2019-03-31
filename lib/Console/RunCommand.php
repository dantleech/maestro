<?php

namespace Phpactor\Extension\Maestro\Console;

use Amp\Delayed;
use Amp\Loop;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Phpactor\ConfigLoader\Core\ConfigLoader;
use Phpactor\Extension\Maestro\Model\ConsolePool;
use Phpactor\Extension\Maestro\Model\Maestro;
use Phpactor\Extension\Maestro\Model\StateMachine\StateMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class RunCommand extends Command
{
    private $maestro;
    private $pool;

    public function __construct(Maestro $maestro, ConsolePool $pool)
    {
        parent::__construct();
        $this->maestro = $maestro;
        $this->pool = $pool;
    }

    protected function configure()
    {
        $this->setName('run');
        $this->addArgument('unit', InputArgument::REQUIRED, 'Unit file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ConfigLoaderBuilder::create()
            ->enableJsonDeserializer('json')
            ->addCandidate(
                Path::makeAbsolute(
                    $input->getArgument('unit'),
                    getcwd()
                ),
                'json'
            )
            ->loader()->load();

        $sectionOutputs = [];

        Loop::repeat(100, function () use ($output, &$sectionOutputs) {
            assert($output instanceof ConsoleOutput);

            foreach ($this->pool->all() as $console) {

                if (!isset($sectionOutputs[$console->name()])) {
                    $sectionOutputs[$console->name()] = $output->section();

                }

                $sectionOutput = $sectionOutputs[$console->name()];
                assert($sectionOutput instanceof ConsoleSectionOutput);

                $sectionOutput->clear();
                $sectionOutput->writeln(sprintf('<info>%s</>', $console->name()));
                $sectionOutput->write($console->tail(5));
            }
        });

        Loop::run(function () use ($config) {
            yield $this->maestro->run($config);
            yield new Delayed(100);
            Loop::stop();
        });
    }
}
