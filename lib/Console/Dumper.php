<?php

namespace Maestro\Console;

use Maestro\Node\Graph;

interface Dumper
{
    public function dump(Graph $graph): string;
}
