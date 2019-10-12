<?php

namespace Maestro\Library\Report;

use Maestro\Library\Graph\Graph;

interface GraphSerializer
{
    public function serialize(Graph $graph): array;
}
