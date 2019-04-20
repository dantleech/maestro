<?php

namespace Maestro\Model;

use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Environment;
use Maestro\Model\Unit\Invoker;

class Maestro
{
    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var ConsoleManager
     */
    private $consoleManager;


    public function __construct(Invoker $invoker, ConsoleManager $consoleManager)
    {
        $this->invoker = $invoker;
        $this->consoleManager = $consoleManager;
    }

    public function run(array $definition)
    {
        $this->invoker->invoke(
            Environment::new(
                $this->consoleManager->new()->id()
            ),
            Definition::fromArray($definition),
        );
    }
}
