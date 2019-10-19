<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Maestro\Library\Instantiator\Instantiator;

class ManifestNode
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $args;

    /**
     * @var ManifestNode[]
     */
    private $nodes = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $taskFqn;

    /**
     * @var array
     */
    private $taskArgs;

    /**
     * @var array
     */
    private $depends;

    public function __construct(
        string $name,
        string $type,
        array $args = [],
        array $nodes = [],
        array $depends = []
    )
    {
        $this->type = $type;
        $this->args = $args;
        $this->nodes = array_map(function (array $data, string $name) {
            return Instantiator::instantiate(ManifestNode::class, array_merge([
                'name' => $name,
            ], $data));
        }, $nodes, array_keys($nodes));
        $this->name = $name;
        $this->taskFqn = $type;
        $this->taskArgs = $args;
        $this->depends = $depends;
    }

    public function args(): array
    {
        return $this->args;
    }

    public function nodes(): array
    {
        return $this->nodes;
    }

    public function type(): string
    {
        return $this->type;
    }

    public static function fromArray(array $data): self
    {
        return Instantiator::instantiate(self::class, $data);
    }

    public function depends(): array
    {
        return $this->depends;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function taskArgs(): array
    {
        return $this->taskArgs;
    }

    public function taskFqn(): string
    {
        return $this->taskFqn;
    }
}
