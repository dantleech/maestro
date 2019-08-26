<?php

namespace Maestro\Loader;

use Maestro\Graph\Scheduler\AsapSchedule;

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
     * @var Schedule
     */
    private $schedule;

    /**
     * @var string[]
     */
    private $tags;

    public function __construct(
        string $type,
        array $args = [],
        array $depends = [],
        array $schedule = [],
        array $tags = []
    ) {
        $this->type = $type;
        $this->args = $args;
        $this->depends = $depends;
        $this->schedule = $schedule ? Instantiator::create()->instantiate(
            Schedule::class,
            $schedule
        ) : new Schedule(AsapSchedule::class);
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

    public function schedule(): Schedule
    {
        return $this->schedule;
    }

    public function tags(): array
    {
        return $this->tags;
    }
}
