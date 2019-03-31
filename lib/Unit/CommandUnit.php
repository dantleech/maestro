<?php

namespace Phpactor\Extension\Maestro\Unit;

use Phpactor\Extension\Maestro\Job\ProcessJob;
use Phpactor\Extension\Maestro\Model\ConsolePool;
use Phpactor\Extension\Maestro\Model\ParameterResolver;
use Phpactor\Extension\Maestro\Model\QueueRegistry;
use Phpactor\Extension\Maestro\Model\Unit;

class CommandUnit implements Unit
{
    const PARAM_COMMAND = 'command';
    const PARAM_CWD = 'cwd';
    const PARAM_CONSOLE = 'console';


    private $queueRegistry;
    private $consolePool;

    public function __construct(ConsolePool $consolePool, QueueRegistry $queueRegistry)
    {
        $this->queueRegistry = $queueRegistry;
        $this->consolePool = $consolePool;
    }

    public function configure(ParameterResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_COMMAND => [],
            self::PARAM_CWD => getcwd(),
            self::PARAM_CONSOLE => 'main',
        ]);

        $resolver->setAllowedTypes(self::PARAM_COMMAND, 'array');
    }

    public function execute(array $params): void
    {
        $console = $this->consolePool->get($params[self::PARAM_CONSOLE]);

        foreach ($params[self::PARAM_COMMAND] as $command) {
            $this->queueRegistry->get($params[self::PARAM_CWD])->enqueue(
                new ProcessJob($console, $command, $params[self::PARAM_CWD])
            );
        }
    }
}
