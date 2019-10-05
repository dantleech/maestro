<?php

namespace Maestro\Extension\Vcs\Report;

use Maestro\Extension\Composer\Survery\ComposerConfigResult;
use Maestro\Extension\Report\Model\ConsoleReport;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Extension\Vcs\Survey\VersionResult;
use Maestro\Library\Composer\PackagistPackageInfo;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Survey\Survey;
use Maestro\Library\Util\StringUtil;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class VersionReport implements ConsoleReport
{
    public function title(): string
    {
        return 'Version Report';
    }

    public function description(): string
    {
        return 'Detailed version overview for each package';
    }

    public function render(OutputInterface $output, Graph $graph): void
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

            $packageReport = $survey->get(ComposerConfigResult::class, new ComposerConfigResult());
            $packagistReport = $survey->get(PackagistPackageInfo::class);
            assert($packagistReport instanceof PackagistPackageInfo);
            $table->addRow([
                $versionReport->packageName(),
                $this->formatConfiguredVersion($versionReport),
                $versionReport->mostRecentTagName(),
                $packageReport->branchAlias(),
                $this->formatPackagistVersion($versionReport, $packagistReport),
                substr($versionReport->mostRecentTagCommitId() ?? '', 0, 10),
                $this->formatHeadCommit($versionReport),
                StringUtil::firstLine((string)$versionReport->headComment()),
            ]);
        }

        $this->renderLegend($output);
        $table->render();
    }

    private function formatConfiguredVersion(VersionResult $versionReport)
    {
        if ($versionReport->willBeTagged()) {
            return sprintf('<bg=black;fg=yellow>%s</>', $versionReport->configuredVersion());
        }

        return $versionReport->configuredVersion();
    }

    private function formatPackagistVersion(VersionResult $versionReport, PackagistPackageInfo $packageReport)
    {
        if ($versionReport->mostRecentTagName() !== $packageReport->latestVersion()) {
            return sprintf('<bg=black;fg=yellow>%s</>', $packageReport->latestVersion());
        }

        return $packageReport->latestVersion();
    }

    private function formatHeadCommit(VersionResult $versionReport)
    {
        if ($versionReport->divergence() > 0) {
            return sprintf(
                '%s <fg=yellow;bg=black>+%s</>',
                substr((string)$versionReport->headId(), 0, 10),
                $versionReport->divergence()
            );
        }

        return substr((string)$versionReport->headId(), 0, 10);
    }

    private function renderLegend(OutputInterface $output): void
    {
        $output->writeln('<info>conf</>: configured version, <info>tag</>: latest tagged version');
        $output->writeln('<info>dev</>: development version (branch alias), <info>reg</>: package registry version');
        $output->writeln('<info>tag-id</>: commit-id of lastest tag, <info>head-id</info>: commit-id of latest commit + number of commits ahead of latest tag');
    }
}
