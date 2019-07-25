<?php

namespace Maestro\Graph\SchedulerRegistry;

use Maestro\Graph\Exception\SchedulerNotFound;
use Maestro\Graph\Schedule;
use Maestro\Graph\Scheduler;
use Maestro\Graph\SchedulerRegistry;

class EagerSchedulerRegistry implements SchedulerRegistry
{
    /**
     * @var array
     */
    private $schedulers = [];

    public function __construct(array $schedulers)
    {
        foreach ($schedulers as $scheduleFqn => $scheduler) {
            $this->add($scheduleFqn, $scheduler);
        }
    }

    public function getFor(Schedule $schedule): Scheduler
    {
        $scheduleFqn = get_class($schedule);
        if (!isset($this->schedulers[$scheduleFqn])) {
            throw new SchedulerNotFound(sprintf(
                'Scheduler for "%s" not registered, schedulers are registered for: "%s"',
                $scheduleFqn,
                implode('", "', array_keys($this->schedulers))
            ));
        }

        return $this->schedulers[$scheduleFqn];
    }

    private function add(string $scheduleFqn, Scheduler $scheduler)
    {
        $this->schedulers[$scheduleFqn] = $scheduler;
    }
}
