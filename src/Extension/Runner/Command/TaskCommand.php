<?php

namespace Maestro\Extension\Runner\Command;

use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Extension\Runner\Console\MethodToInputDefinitionConverter;
use Maestro\Extension\Task\Extension\TaskHandlerDefinition;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Queue;
use Maestro\Library\Util\Cast;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
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

    /**
     * @var MethodToInputDefinitionConverter
     */
    private $methodToDefinitionConverter;

    public function __construct(
        GraphBehavior $behavior,
        TaskHandlerDefinitionMap $definitionMap,
        MethodToInputDefinitionConverter $methodToDefinitionConverter
    ) {
        $this->definitionMap = $definitionMap;
        $this->behavior = $behavior;
        $this->methodToDefinitionConverter = $methodToDefinitionConverter;
        parent::__construct();
    }

    protected function configure()
    {
        $this->behavior->configure($this);
        $this->setDescription('Run the plan and execute task on each leaf');
        $this->addArgument('task', InputArgument::REQUIRED);
        $this->ignoreValidationErrors();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reports = $this->behavior->fetchReports($input);

        $taskName = Cast::toString($input->getArgument('task'));
        $definition = $this->definitionMap->getDefinitionByAlias($taskName);
        $taskInput = $this->bindNewInput($input, $taskName, $definition);
        $task = Instantiator::instantiate($definition->taskClass(), $taskInput->getOptions());

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
        $this->behavior->renderReports($graph, ...$reports);

        return $graph->nodes()->byState(State::FAILED())->count();
    }

    private function bindNewInput(InputInterface $input, string $taskName, TaskHandlerDefinition $definition)
    {
        if (!method_exists($input, '__toString')) {
            throw new RuntimeException(sprintf(
                'Input class "%s" must have a __toString method returning the input as a string',
                get_class($input)
            ));
        }
        $fullString = $input->__toString();
        preg_match(
            sprintf('{(%s\s+%s)}', $this->getName(), $taskName),
            $fullString,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        $taskParams = substr($fullString, $matches[0][1] + strlen($matches[0][0]));
        $input = new StringInput($taskParams);
        $inputDefinition = $this->methodToDefinitionConverter->inputDefinitionFor($definition->taskClass(), '__construct');
        $input->bind($inputDefinition);
        $input->validate();
        return $input;
    }
}
