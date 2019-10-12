<?php

namespace Maestro\Library\Survey;

use Amp\Promise;

interface Surveyor
{
    /**
     * Promise must yield \Maestro\Extension\Survey\Model\SurveyResult>
     *
     * return Promise<Artifact[]>
     *
    /** public function __invoke(...): Promise; */

    public function description(): string;
}
