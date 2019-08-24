<?php

namespace Maestro\Extension\Survey\Task;

use Maestro\Graph\Task;

class PackageSurveyTask implements Task
{
    public function description(): string
    {
        return 'gathering package information';
    }
}
