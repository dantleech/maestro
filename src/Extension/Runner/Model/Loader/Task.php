<?php

namespace Maestro\Extension\Runner\Model\Loader;

class Task
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
     * @var array
     */
    private $depends;

    /**
     * @var string[]
     */
    private $tags;

    public function __construct(
        string $type,
        array $args = [],
        array $depends = [],
        array $tags = []
    ) {
        $this->type = $type;
        $this->args = $args;
        $this->depends = $depends;
        $this->tags = $tags;
    }

    public function args(): array
    {
        return $this->args;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function depends(): array
    {
        return $this->depends;
    }

    public function tags(): array
    {
        return $this->tags;
    }
}
