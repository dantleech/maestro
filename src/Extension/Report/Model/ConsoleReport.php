<?php

namespace Maestro\Extension\Report\Model;

use Maestro\Library\Graph\Graph;
use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleReport
{
    public function render(OutputInterface $output, Graph $graph): void;

    public function title(): string;

    public function description(): string;
}
