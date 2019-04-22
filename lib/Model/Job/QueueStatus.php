<?php

namespace Maestro\Model\Job;

use DateTimeImmutable;

class QueueStatus
{
    /** @var bool */
    public $success = true;

    /**
     * @var string
     */
    public $id;

    /**
     * @var int
     */
    public $code = 0;

    /**
     * @var string
     */
    public $message = null;

    /**
     * @var DateTimeImmutable
     */
    public $start;

    /**
     * @var DateTimeImmutable
     */
    public $end;
}
