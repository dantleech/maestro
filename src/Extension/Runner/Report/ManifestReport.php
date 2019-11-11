<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\json_encode;

class ManifestReport implements Report
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $manifest;

    public function __construct(OutputInterface $output, array $manifest)
    {
        $this->output = $output;
        $this->manifest = $manifest;
    }

    public function render(Graph $graph): void
    {
        $this->output->writeln(json_encode($this->manifest));
    }
}
