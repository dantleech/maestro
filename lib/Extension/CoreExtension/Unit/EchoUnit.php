<?php

namespace Maestro\Extension\CoreExtension\Unit;

use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Unit\Config;
use Maestro\Model\Unit\Environment;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Unit;

class EchoUnit implements Unit
{
    /**
     * @var ConsoleManager
     */
    private $consoles;

    public function __construct(ConsoleManager $consoles)
    {
        $this->consoles = $consoles;
    }

    public function configure(Config $config)
    {
        $config->setRequired([
            'message'
        ]);
    }

    public function execute(Environment $environment, array $config)
    {
        $this->consoles->get(
            $environment->group()
        )->write($config['message']);
    }
}
