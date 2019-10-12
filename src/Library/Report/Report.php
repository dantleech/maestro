<?php

namespace Maestro\Library\Report;

use Maestro\Library\Graph\Graph;

interface Report
{
    public function render(Graph $graph): void;
}
