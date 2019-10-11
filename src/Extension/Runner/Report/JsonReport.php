<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Edges;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Report\Report;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class JsonReport implements Report
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var TaskHandlerDefinitionMap
     */
    private $definitionMap;

    /**
     * @var string
     */
    private $directory;

    public function __construct(OutputInterface $output, TaskHandlerDefinitionMap $definitionMap, string $directory)
    {
        $this->output = $output;
        $this->definitionMap = $definitionMap;
        $this->directory = $directory;
    }

    public function render(Graph $graph): void
    {
        $filePath = Path::join([$this->directory, 'graph-report.json']);
        $json = json_encode($this->graphToArray($graph), JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);
        $this->output->writeln(sprintf('<info>Writing JSON report to</info>: %s', $filePath));
    }

    public function description(): string
    {
        return 'renders the whole graph in JSON';
    }

    private function graphToArray(Graph $graph)
    {
        return [
            'edges' => $this->serializeEdges($graph->edges()),
            'nodes' => $this->serializeNodes($graph->nodes()),
        ];
    }

    private function className(object $object)
    {
        return str_replace('\\', '-', get_class($object));
    }

    private function serializeEdges(Edges $edges)
    {
        return array_map(function (Edge $edge) {
            return [
                'from' => $edge->from(),
                'to' => $edge->to()
            ];
        }, $edges->toArray());
    }

    private function serializeNodes(Nodes $nodes)
    {
        return array_map(function (Node $node) {
            $exception = $node->exception();
            return [
                'id' => $node->id(),
                'label' => $node->label(),
                'state' => $node->state()->toString(),
                'exception' => $exception ? $exception->__toString() : '',
                'tags' => $node->tags(),
                'task' => [
                    'alias' => $this->definitionMap->getDefinitionByClass(get_class($node->task()))->alias(),
                    'description' => $node->task()->description(),
                    'class' => $this->className($node->task()),
                    'args' => $this->serializeTaskArgs($node->task()),
                ],
                'artifacts' => $this->serializeArtifacts($node->artifacts()),
            ];
        }, $nodes->toArray());
    }

    private function serializeArtifacts(Artifacts $artifacts): array
    {
        return (array)array_combine(
            array_map(function (Artifact $artifact) {
                return $this->className($artifact);
            }, $artifacts->toArray()),
            array_map(function (Artifact $artifact) {
                return $artifact->serialize();
            }, $artifacts->toArray())
        );
    }

    private function serializeTaskArgs(object $task): array
    {
        $reflection = new ReflectionClass($task);
        $properties = $reflection->getProperties();

        return (array)array_combine(
            array_map(function (ReflectionProperty $property) {
                return $property->getName();
            }, $properties),
            array_map(function (ReflectionProperty $property) use ($task) {
                $property->setAccessible(true);
                return $property->getValue($task);
            }, $properties)
        );
    }
}
