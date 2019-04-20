<?php

namespace Maestro\Console\Command;

use Amp\Delayed;
use Amp\Loop;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Environment;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Unit\Parameters;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class Run extends Command
{
    /**
     * @var Maestro
     */
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

        $this->maestro->run($config);
    }
}
