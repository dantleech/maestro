<?php

namespace Maestro\Extension\Serializer\Report;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class JsonReport implements Report
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(SerializerInterface $serializer, OutputInterface $output)
    {
        $this->serializer = $serializer;
        $this->output = $output;
    }

    public function render(Graph $graph): void
    {
        $this->output->write(
            $this->serializer->serialize($graph, 'json'),
            false,
            OutputInterface::OUTPUT_RAW
        );
    }
}
