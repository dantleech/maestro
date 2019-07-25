<?php

namespace Maestro\Console;

use Maestro\Graph\Graph;

interface Dumper
{
    public function dump(Graph $graph): string;
}
