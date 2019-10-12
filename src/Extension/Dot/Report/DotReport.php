<?php

namespace Maestro\Extension\Dot\Report;

use Maestro\Library\Report\Report;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\file_put_contents;
use Webmozart\PathUtil\Path;

class DotReport implements Report
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(string $directory, OutputInterface $output)
    {
        $this->directory = $directory;
        $this->output = $output;
    }

    public function render(Graph $graph): void
    {
        $dotContents = $this->buildDotFileContents($graph);
        $outputPath = Path::join([$this->directory, 'maestro.dot']);
        $imageFileName = 'maestro.png';

        $this->output->writeln(sprintf('<info>Writing dot file to:</info> %s', $outputPath));
        file_put_contents($outputPath, $dotContents);
        $command = sprintf('dot %s -Tpng -o %s', $outputPath, $imageFileName);
        $this->output->writeln(sprintf('<info>Generate the image with:</info> %s', $command));
    }

    private function buildDotFileContents(Graph $graph)
    {
        $lines = [
            'digraph maestro {'
        ];
        $lines[] = '  rankdir=TB';

        foreach ($graph->nodes() as $node) {
            $lines[] = sprintf(
                '  "%s" [color=%s label=<<b>%s</b> (%s) %s>]',
                $node->id(),
                $node->state()->isFailed() ? 'red' : 'black',
                $node->label(),
                $node->state()->toString(),
                $this->buildNodeMetaHtml($node)
            );
        }
        foreach ($graph->edges() as $edge) {
            $nodeFrom = $graph->nodes()->get($edge->from());
            $nodeTo = $graph->nodes()->get($edge->to());
            $lines[] = sprintf('  "%s"->"%s"', $edge->to(), $edge->from());
        }
        $lines[] = '}';

        return implode(PHP_EOL, $lines);
    }

    private function buildNodeMetaHtml(Node $node)
    {
        $tags = $node->tags();
        if ($tags) {
            $html[] = sprintf('<font point-size=\'12\' color="darkgreen"><b>%s</b></font>', implode(',', $tags));
        }
        $html[] = '<br/>';
        $html[] = '<font point-size=\'10\'>';
        $html[] = sprintf(
            '<font color=\'blue\'>was %s</font><br/>',
            addslashes($node->task()->description())
        );
        foreach ($node->artifacts() as $artifact) {
            $html[] = sprintf('Artifact: <i>%s</i><br/>', addslashes(get_class($artifact)));
        }

        $html[] = '</font>';

        return implode('', $html);
    }
}
