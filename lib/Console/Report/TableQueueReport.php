<?php

namespace Maestro\Console\Report;

use Maestro\Model\Job\QueueStatus;
use Maestro\Model\Job\QueueStatuses;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class TableQueueReport implements QueueReport
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function render(QueueStatuses $statuses)
    {
        $table = new Table($this->output);
        $table->setHeaders([
            'id', 'status', 'last line'
        ]);
        
        foreach ($statuses as $status) {
            assert($status instanceof QueueStatus);
            $interval = $status->start()->diff($status->end());
            $table->addRow([
                $status->id(),
                sprintf(
                    '%ds %s => %s',
                    $interval->s + ($interval->m * 60),
                    $status->state(),
                    $status->code()
                    ),
                substr(trim((string) $status->message()), 0, 80),
            ]);
        }
        
        $table->render();
    }
}
