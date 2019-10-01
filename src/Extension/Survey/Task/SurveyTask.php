<?php

namespace Maestro\Extension\Survey\Task;

use Maestro\Library\Task\Task;

class SurveyTask implements Task
{
    public function description(): string
    {
        return 'surveying';
    }
}
