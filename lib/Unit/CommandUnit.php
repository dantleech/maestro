<?php

namespace Phpactor\Extension\Maestro\Unit;

use Phpactor\Extension\Maestro\Job\Process\ProcessJob;
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

    public function __construct(QueueRegistry $queueRegistry)
    {
        $this->queueRegistry = $queueRegistry;
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
        foreach ($params[self::PARAM_COMMAND] as $command) {
            $this->queueRegistry->get($params[self::PARAM_CWD])->enqueue(
                new ProcessJob($command, $params[self::PARAM_CWD], $params[self::PARAM_CONSOLE])
            );
        }
    }
}
