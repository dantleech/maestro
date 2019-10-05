<?php

namespace Maestro\Extension\Report\Model;

use Maestro\Extension\Report\Model\Exception\ReportNotFound;

class ConsoleReportRegistry
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

    public function get(string $name): ConsoleReport
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

    private function add(string $name, ConsoleReport $report)
    {
        $this->reports[$name] = $report;
    }
}
