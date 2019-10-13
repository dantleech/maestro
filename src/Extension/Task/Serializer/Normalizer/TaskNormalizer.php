<?php

namespace Maestro\Extension\Task\Serializer\Normalizer;

use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Task\Task;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TaskNormalizer implements NormalizerInterface
{
    /**
     * @var TaskHandlerDefinitionMap
     */
    private $map;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(TaskHandlerDefinitionMap $map, NormalizerInterface $normalizer)
    {
        $this->map = $map;
        $this->normalizer = $normalizer;
    }
    /**
     * {@inheritDoc}
     */
    public function normalize($task, $format = null, array $context = [
    ])
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
