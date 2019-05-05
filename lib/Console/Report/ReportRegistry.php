<?php

namespace Maestro\Console\Report;

use Maestro\Console\Progress\Exception\ProgressNotFound;
use Maestro\Console\Report\Exception\ReportNotFound;

class ReportRegistry
{
    /**
     * @var QueueReport[]
     */
    private $reports = [];

    public function __construct(array $progressMap)
    {
        foreach ($progressMap as $name => $progress) {
            $this->add($name, $progress);
        }
    }

    public function get(string $name): QueueReport
    {
        if (!isset($this->reports[$name])) {
            throw new ReportNotFound(sprintf(
                'Report "%s" not found, known reports "%s"',
                $name,
                implode('", "', array_keys($this->reports))
            ));
        }

        return $this->reports[$name];
    }

    private function add(string $name, QueueReport $progress)
    {
        $this->reports[$name] = $progress;
    }
}
