<?php

namespace Maestro\Extension\Survey\Model;

class SurveyBuilder
{
    private $results = [];

    public function addResult($result)
    {
        $this->results[] = $result;
    }

    public function build(): Survey
    {
        return new Survey($this->results);
    }
}
