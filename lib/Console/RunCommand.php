<?php

namespace Maestro\Console;

use Amp\Delayed;
use Amp\Loop;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Unit\Parameters;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class RunCommand extends Command
{
    /**
     * @var Invoker
     */
    private $invoker;

    public function __construct(Invoker $invoker)
    {
        parent::__construct();
        $this->invoker = $invoker;
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

        $this->invoker->invoke(Definition::fromArray($config), Parameters::new());
    }
}
