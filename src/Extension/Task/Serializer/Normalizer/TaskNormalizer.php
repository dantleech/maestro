<?php

namespace Maestro\Extension\Task\Serializer\Normalizer;

use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Task\Task;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class TaskNormalizer implements NormalizerInterface
{
    /**
     * @var TaskHandlerDefinitionMap
     */
    private $map;

    /**
     * @var PropertyNormalizer
     */
    private $normalizer;


    public function __construct(TaskHandlerDefinitionMap $map, PropertyNormalizer $normalizer)
    {
        $this->map = $map;
        $this->normalizer = $normalizer;
    }
    /**
     * {@inheritDoc}
     */
    public function normalize($task, $format = null, array $context = array (
    ))
    {
        assert($task instanceof Task);

        return [
            'alias' => $this->map->getDefinitionByClass(get_class($task))->alias(),
            'description' => $task->description(),
            'class' => get_class($task),
            'args' => $this->normalizer->normalize($task),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Task;
    }
}
