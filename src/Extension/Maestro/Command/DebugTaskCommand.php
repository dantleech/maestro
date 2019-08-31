<?php

namespace Maestro\Extension\Maestro\Command;

use Maestro\Extension\Maestro\Container\TaskHandlerDefinition;
use Maestro\Extension\Maestro\Container\TaskHandlerDefinitionMap;
use Maestro\Util\Cast;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugTaskCommand extends Command
{
    /**
     * @var TaskHandlerDefinitionMap<TaskHandlerDefinition>
     */
    private $map;

    public function __construct(TaskHandlerDefinitionMap $map)
    {
        parent::__construct();
        $this->map = $map;
    }

    protected function configure()
    {
        $this->setDescription('Show available tasks');
        $this->addArgument('task', InputArgument::OPTIONAL, 'Show information about task');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($taskAlias = Cast::toStringOrNull($input->getArgument('task'))) {
            $this->showTask($output, $taskAlias);
            return 0;
        }
        $this->listTasks($output);
        return 0;
    }

    private function listTasks(OutputInterface $output): void
    {
        $output->writeln('<comment>Registered task aliases</>:');
        foreach ($this->map->sorted() as $definition) {
            $output->writeln($definition->alias());
        }
    }

    private function showTask(OutputInterface $output, $taskAlias): void
    {
        $definition = $this->map->getDefinitionByAlias($taskAlias);
        $output->writeln(sprintf('<comment>Alias</>: %s', $taskAlias));
        $output->writeln(sprintf('<comment>Task FQN</>: %s', $definition->taskClass()));
        $output->writeln(sprintf('<comment>Handler service</>: %s', $definition->serviceId()));

        $reflection = new ReflectionClass($definition->taskClass());

        $output->writeln('<comment>Arguments:</comment>');
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            $output->writeln('This task has no arguments');
            return;
        }

        $params = $constructor->getParameters();
        $definition = [];
        foreach ($params as $param) {
            $output->writeln(sprintf(' - %s (%s)', $param->getName(), (string)$param->getType()));
        }
    }
}
