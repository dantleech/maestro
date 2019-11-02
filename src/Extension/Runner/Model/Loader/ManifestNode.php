<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Task\NullTask;

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

    /**
     * @var array
     */
    private $tags;

    /**
     * @var string|null
     */
    private $include;

    /**
     * @var array
     */
    private $vars;

    public function __construct(
        string $name,
        string $type,
        array $vars = [],
        array $args = [],
        array $nodes = [],
        array $depends = [],
        array $tags = [],
        string $include = null
    ) {
        $this->type = $type;
        $this->args = $args;
        $this->nodes = array_map(function (array $data, string $name) {
            return Instantiator::instantiate(ManifestNode::class, array_merge([
                'name' => $name,
                'type' => NullTask::class,
            ], $data));
        }, $nodes, array_keys($nodes));
        $this->name = $name;
        $this->depends = $depends;
        $this->tags = $tags;
        $this->include = $include;
        $this->vars = $vars;
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

    public function tags(): array
    {
        return $this->tags;
    }
}
