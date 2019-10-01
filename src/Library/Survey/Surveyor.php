<?php

namespace Maestro\Library\Survey;

use Amp\Promise;
use Maestro\Graph\Environment;

interface Surveyor
{
    /**
     * Promise must yield \Maestro\Extension\Survey\Model\SurveyResult>
     *
     * return Promise<SurveyResult>
     *
    /** public function __invoke(...): Promise; */

    public function description(): string;
}
