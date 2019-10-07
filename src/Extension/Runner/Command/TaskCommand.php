<?php

namespace Maestro\Extension\Runner\Command;

use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Extension\Runner\Report\RunReport;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Node;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Queue;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TaskCommand extends Command
{
    /**
     * @var TaskHandlerDefinitionMap
     */
    private $definitionMap;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var GraphBehavior
     */
    private $behavior;

    public function __construct(
        GraphBehavior $behavior,
        TaskHandlerDefinitionMap $definitionMap
    ) {
        $this->definitionMap = $definitionMap;
        $this->behavior = $behavior;
        parent::__construct();
    }

    protected function configure()
    {
        $this->behavior->configure($this);
        $this->addArgument('task', InputArgument::REQUIRED);
        $this->ignoreValidationErrors();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskName = $input->getArgument('task');

        $definition = $this->definitionMap->getDefinitionByAlias($taskName);

        $reflection = new ReflectionClass($definition->taskClass());
        $constructor = $reflection->getMethod('__construct');

        $inputDefinition = $this->getDefinition();

        $params = [];
        foreach ($constructor->getParameters() as $parameter) {
            $params[] = $parameter->getName();
            $inputDefinition->addOption(
                new InputOption($parameter->getName(), null, InputOption::VALUE_REQUIRED)
            );
        }

        $input->bind($inputDefinition);
        $input->validate();
        $parameters = array_intersect_key($input->getOptions(), array_combine($params, $params));
        $task = Instantiator::instantiate($definition->taskClass(), $parameters);

        $graph = $this->behavior->loadGraph($input);
        $builder = $graph->builder();

        foreach ($graph->leafs() as $leaf) {
            $builder->addNode($node = Node::create(uniqid(), [
                'task' => $task
            ]));
            $builder->addEdge(Edge::create($node->id(), $leaf->id()));
        }
        $graph = $builder->build();
        $this->behavior->run($input, $output, $graph);
        $report = new RunReport();
        $report->render($output, $graph);
    }
}
