<?php

namespace Maestro\Extension\Graph\Report;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Report\Report;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NodeReport implements Report
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var NormalizerInterface
     */
    private $serializer;

    public function __construct(OutputInterface $output, NormalizerInterface $serializer)
    {
        $this->output = $output;
        $this->serializer = $serializer;
    }

    public function render(Graph $graph): void
    {
        $table = new Table($this->output);

        $header = [];
        foreach ($graph->nodes() as $node) {
            $rowData = array_filter(
                $this->flatten((array)$this->serializer->normalize($node)),
                function ($key) {
                    return in_array($key, [
                        'id',
                        'label',
                        'tags',
                    ]);
                },
                ARRAY_FILTER_USE_KEY
            );
            if (!$header) {
                $header = array_keys($rowData);
            }
            $table->addRow($rowData);
        }
        $table->setHeaders($header);
        $table->render();
    }

    private function flatten(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return json_encode($value);
            }
            return $value;
        }, $data);
    }
}
