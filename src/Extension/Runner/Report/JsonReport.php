<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Report\Report;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\PathUtil\Path;
use function Safe\file_put_contents;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SerializerInterface $serializer, string $directory, LoggerInterface $logger)
    {
        $this->directory = $directory;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function render(Graph $graph): void
    {
        $content = $this->serializer->serialize($graph, 'json');
        $filePath = Path::join([$this->directory, 'graph-report.json']);
        $this->logger->notice(sprintf('Writing JSON report to: %s', $filePath));
        file_put_contents($filePath, $content);
    }
}
