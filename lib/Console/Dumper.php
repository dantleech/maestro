<?php

namespace Maestro\Console;

use Maestro\Task\Graph;

interface Dumper
{
    public function dump(Graph $graph): string;
}
