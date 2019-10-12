<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Report\GraphSerializer;
use Maestro\Library\Report\Report;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;
use Webmozart\PathUtil\Path;

class JsonReport implements Report
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var GraphSerializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Serializer $serializer, string $directory, LoggerInterface $logger)
    {
        $this->directory = $directory;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function render(Graph $graph): void
    {
        $filePath = Path::join([$this->directory, 'graph-report.json']);
        echo $this->serializer->serialize($graph, 'json');
        $this->logger->notice(sprintf('Writing JSON report to: %s', $filePath));
    }
}
