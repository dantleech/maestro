<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Report\GraphSerializer;
use Maestro\Library\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class JsonReport implements Report
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var GraphSerializer
     */
    private $serializer;

    public function __construct(OutputInterface $output, GraphSerializer $serializer, string $directory)
    {
        $this->output = $output;
        $this->directory = $directory;
        $this->serializer = $serializer;
    }

    public function render(Graph $graph): void
    {
        $filePath = Path::join([$this->directory, 'graph-report.json']);
        echo $json = json_encode($this->serializer->serialize($graph));
        file_put_contents($filePath, $json);
        //$this->output->writeln(sprintf('<info>Writing JSON report to</info>: %s', $filePath));
    }
}
