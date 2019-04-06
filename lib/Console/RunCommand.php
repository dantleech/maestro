<?php

namespace Maestro\Console;

use Amp\Delayed;
use Amp\Loop;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class RunCommand extends Command
{
    private $maestro;

    public function __construct(Maestro $maestro)
    {
        parent::__construct();
        $this->maestro = $maestro;
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

        Loop::run(function () use ($config) {
            yield $this->maestro->run($config);
            yield new Delayed(100);
            Loop::stop();
        });
    }
}
