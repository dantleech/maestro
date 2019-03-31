<?php

namespace Phpactor\Extension\Maestro\Unit;

use Amp\Process\Process;
use Phpactor\Extension\Maestro\Job\Process\ProcessJob;
use Phpactor\Extension\Maestro\Model\Console;
use Phpactor\Extension\Maestro\Model\ConsolePool;
use Phpactor\Extension\Maestro\Model\ParameterResolver;
use Phpactor\Extension\Maestro\Model\QueueRegistry;
use Phpactor\Extension\Maestro\Model\Unit;
use Phpactor\Extension\Maestro\Model\UnitExecutor;
use Phpactor\TestUtils\Workspace;
use Webmozart\PathUtil\Path;

class PackageWorkspaceUnit implements Unit
{
    const PARAM_PACKAGES = 'packages';
    const PARAM_RESET = 'reset';
    const PARAM_CONSOLE = 'console';


    /**
     * @var UnitExecutor
     */
    private $executor;

    /**
     * @var string
     */
    private $workspacePath;

    /**
     * @var QueueRegistry
     */
    private $registry;

    /**
     * @var ConsolePool
     */
    private $consolePool;

    public function __construct(
        UnitExecutor $executor,
        QueueRegistry $registry,
        ConsolePool $consolePool,
        string $workspacePath
    )
    {
        $this->executor = $executor;
        $this->workspacePath = $workspacePath;
        $this->registry = $registry;
        $this->consolePool = $consolePool;
    }

    public function configure(ParameterResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_PACKAGES => [],
            self::PARAM_RESET => false,
            self::PARAM_CONSOLE => 'main',
        ]);
        $resolver->setAllowedTypes(self::PARAM_PACKAGES, ['array']);
    }

    public function execute(array $params): void
    {
        $packages = $params[self::PARAM_PACKAGES];

        foreach ($packages as $repoUrl => $packageUnitConfig) {
            $path = Path::join($this->workspacePath, md5($repoUrl));

            $console = $this->consolePool->get($repoUrl);
            $console->write(sprintf('Building "%s" in "%s"'.PHP_EOL, $repoUrl, $path));
            $packageUnitConfig['cwd'] = $path;
            $packageUnitConfig['console'] = $console->name();

            $this->initPackage($console, $path, $repoUrl, $packageUnitConfig, $params[self::PARAM_RESET]);

            $this->executor->execute($packageUnitConfig);
        }
    }

    private function initPackage(Console $console, string $path, string $repoUrl, array $packageUnitConfig, bool $reset)
    {
        $queue = $this->registry->createQueue($path);

        if ($reset) {
            Workspace::create($path)->reset();
        }

        $queue->enqueue(
            new ProcessJob(sprintf(
                'git clone git@github.com:%s %s',
                $repoUrl,
                $path,
            ), $this->workspacePath, $console->name())
        );
    }
}
