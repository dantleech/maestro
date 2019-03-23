<?php

namespace Phpactor\Extension\Maestro\Console;

use Phpactor\Extension\Maestro\Model\Maestro;
use Phpactor\Extension\Maestro\Model\StateMachine\StateMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->maestro->run();
    }
}
