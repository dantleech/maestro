<?php

namespace Maestro\Library\Survey;

use Amp\Promise;
use Maestro\Graph\Environment;

interface Surveyor
{
    /**
     * Promise must yield \Maestro\Extension\Survey\Model\SurveyResult>
     *
     * @return Promise
     */
    public function survey(Environment $environment): Promise;
}
