<?php

namespace Maestro\Console\Progress;

use Maestro\Console\Progress\Exception\ProgressNotFound;

class ProgressRegistry
{
    /**
     * @var array
     */
    private $progresses = [];

    public function __construct(array $progressMap)
    {
        foreach ($progressMap as $name => $progress) {
            $this->add($name, $progress);
        }
    }

    public function get(string $name): Progress
    {
        if (!isset($this->progresses[$name])) {
            throw new ProgressNotFound(sprintf(
                'Progress "%s" not found, known progresses "%s"',
                $name,
                implode('", "', array_keys($this->progresses))
            ));
        }

        return $this->progresses[$name];
    }

    private function add(string $name, Progress $progress)
    {
        $this->progresses[$name] = $progress;
    }
}
