<?php

namespace Maestro\Extension\Survey\Model;

use Amp\Promise;
use Maestro\Graph\Environment;
use Maestro\Package\Package;

interface Surveyor
{
    /**
     * Promise must yield \Maestro\Extension\Survey\Model\SurveyResult>
     *
     * @return Promise
     */
    public function survey(Environment $environment): Promise;
}
