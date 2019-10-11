<?php

namespace Maestro\Library\Report;

use Maestro\Library\Report\Exception\ReportNotFound;

class ReportRegistry
{
    /**
     * @var array
     */
    private $reports;

    public function __construct(array $reports)
    {
        foreach ($reports as $name => $report) {
            $this->add($name, $report);
        }
    }

    public function get(string $name): Report
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

    private function add(string $name, Report $report)
    {
        $this->reports[$name] = $report;
    }
}
