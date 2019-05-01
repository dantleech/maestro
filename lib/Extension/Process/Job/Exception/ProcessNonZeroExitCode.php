<?php

namespace Maestro\Extension\Process\Job\Exception;

use Maestro\Model\Job\QueueDispatcher\Exception\JobFailure;

class ProcessNonZeroExitCode extends JobFailure
{
}
