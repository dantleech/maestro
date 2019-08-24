<?php

namespace Maestro\Extension\Version\Console;

use Maestro\Extension\Composer\Model\PackagistPackageInfo;
use Maestro\Extension\Survey\Model\Survey;
use Maestro\Extension\Version\Survey\PackageResult;
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
            'dev',
            'reg',
            'tag-id',
            'head-id',
            'message',
        ]);

        foreach ($graph->nodes()->byTaskResult(TaskResult::SUCCESS())->byTags('survey') as $node) {
            $survey = $node->environment()->vars()->get('survey');
            assert($survey instanceof Survey);
            $versionReport = $survey->get(VcsResult::class);
            $packageReport = $survey->get(PackageResult::class, new PackageResult());
            assert($versionReport instanceof VcsResult);
            $packagistReport = $survey->get(PackagistPackageInfo::class);
            assert($packagistReport instanceof PackagistPackageInfo);
            $table->addRow([
                $versionReport->packageName(),
                $this->formatConfiguredVersion($versionReport),
                $versionReport->taggedVersion(),
                $packageReport->branchAlias(),
                $this->formatPackagistVersion($versionReport, $packagistReport),
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

    private function formatPackagistVersion(VcsResult $versionReport, PackagistPackageInfo $packageReport)
    {
        if ($versionReport->taggedVersion() !== $packageReport->latestVersion()) {
            return sprintf('<bg=black;fg=yellow>%s</>', $packageReport->latestVersion());
        }

        return $packageReport->latestVersion();
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
