<?php

namespace Maestro\Extension\Report\Model;

use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Edges;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Report\GraphSerializer;
use ReflectionClass;
use ReflectionProperty;

class MaestroGraphSerializer implements GraphSerializer
{
    /**
     * @var TaskHandlerDefinitionMap
     */
    private $map;

    public function __construct(TaskHandlerDefinitionMap $map)
    {
        $this->map = $map;
    }

    public function serialize(Graph $graph): array
    {
        return [
            'edges' => $this->serializeEdges($graph->edges()),
            'nodes' => $this->serializeNodes($graph->nodes()),
        ];
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
                    'alias' => $this->map->getDefinitionByClass(get_class($node->task()))->alias(),
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
                return $this->serializeArtifact($artifact);
            }, $artifacts->toArray())
        );
    }

    private function serializeArtifact(Artifact $artifact)
    {
        $reflectionClass = new ReflectionClass($artifact);
        $properties = $reflectionClass->getProperties();
        $properties = array_filter($properties, function (ReflectionProperty $property) {
            return $property->isPublic();
        });

        return array_combine(
            array_map(function (ReflectionProperty $property) {
                return $property->getName();
            }, $properties),
            array_map(function (ReflectionProperty $property) use ($artifact) {
                return $property->getValue($artifact);
            }, $properties)
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

    private function className(object $object)
    {
        return str_replace('\\', '-', get_class($object));
    }
}
