<?php

namespace Maestro\Tests\Integration;

use Maestro\MaestroExtension;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Webmozart\PathUtil\Path;

class IntegrationTestCase extends TestCase
{
    protected function initWorkspace()
    {
        $this->workspace()->reset();
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }

    protected function container(): Container
    {
        return PhpactorContainer::fromExtensions([
            ConsoleExtension::class,
            MaestroExtension::class,

        ], [
            MaestroExtension::PARAM_WORKSPACE_PATH => $this->packageWorkspacePath(),
            MaestroExtension::PARAM_TEMPLATE_PATHS => [
                $this->workspace()->path('/')
            ]
        ]);
    }

    protected function packageWorkspacePath(string $subPath = ''): string
    {
        return $this->workspace()->path(Path::join(['Package', $subPath]));
    }
}
