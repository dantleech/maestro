<?php

namespace Maestro\Extension\Survey\Task;

use Maestro\Graph\Task;

class SurveyTask implements Task
{
    public function description(): string
    {
        return 'surveying';
    }
}
