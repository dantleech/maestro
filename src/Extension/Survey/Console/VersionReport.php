<?php

namespace Maestro\Extension\Survey\Console;

use Maestro\Extension\Version\Survey\VcsResult;
use Maestro\Graph\Graph;
use Maestro\Graph\TaskResult;
use Maestro\Util\StringUtil;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class VersionReport
{
    public function render(OutputInterface $output, Graph $graph)
    {
        $table = new Table($output);
        $table->setHeaders([
            'package',
            'conf',
            'tag',
            'tag-id',
            'head-id',
            'message',
        ]);

        foreach ($graph->nodes()->byTaskResult(TaskResult::SUCCESS())->byTags('survey') as $node) {
            $versionReport = $node->environment()->vars()->get('survey')->get(VcsResult::class);
            assert($versionReport instanceof VcsResult);
            $table->addRow([
                $versionReport->packageName(),
                $this->formatConfiguredVersion($versionReport),
                $versionReport->taggedVersion(),
                substr($versionReport->taggedCommit() ?? '', 0, 10),
                $this->formatHeadCommit($versionReport),
                StringUtil::firstLine($versionReport->headMessage()),
            ]);
        }
        $table->render();
    }

    private function formatConfiguredVersion(VcsResult $versionReport)
    {
        if ($versionReport->willBeTagged()) {
            return sprintf('<bg=black;fg=yellow>%s</>', $versionReport->configuredVersion());
        }

        return $versionReport->configuredVersion();
    }

    private function formatHeadCommit(VcsResult $versionReport)
    {
        if ($versionReport->divergence() > 0) {
            return sprintf(
                '%s <fg=yellow;bg=black>+%s</>',
                substr($versionReport->headCommit(), 0, 10),
                $versionReport->divergence()
            );
        }

        return substr($versionReport->headCommit(), 0, 10);
    }
}
