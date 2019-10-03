<?php

namespace Maestro\Extension\Version\Console;

use Maestro\Extension\Report\Model\ConsoleReport;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Survey\Survey;
use Symfony\Component\Console\Output\OutputInterface;

class VersionReport implements ConsoleReport
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

        foreach ($graph->nodes()->byTaskClass(SurveyTask::class) as $node) {
            assert($node instanceof Node);
            $artifacts = $node->artifacts();
            if (!$artifacts->has(Survey::class)) {
                continue;
            }
            $survey = $artifacts->get(Survey::class);
            assert($survey instanceof Survey);

            $versionReport = $survey->get(VersionResult::class);
            assert($versionReport instanceof VersionResult);

            $packageReport = $survey->get(PackageResult::class, new PackageResult());

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

        $this->renderLegend($output);
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

    private function renderLegend(OutputInterface $output): void
    {
        $output->writeln('<info>conf</>: configured version, <info>tag</>: latest tagged version');
        $output->writeln('<info>dev</>: development version (branch alias), <info>reg</>: package registry version');
        $output->writeln('<info>tag-id</>: commit-id of lastest tag, <info>head-id</info>: commit-id of latest commit + number of commits ahead of latest tag');
    }
}<Paste>
