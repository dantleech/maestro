<?php

namespace Maestro\Tests\Integration;

use Maestro\Extension\Template\TemplateExtension;
use Maestro\MaestroExtension;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\TestUtils\Workspace;
use Webmozart\PathUtil\Path;

class IntegrationTestCase extends TestCase
{
    const EXAMPLE_NAMESPACE = 'test-namespace';

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
            TemplateExtension::class,

        ], [
            MaestroExtension::PARAM_WORKSPACE_PATH => $this->workspace()->path(''),
            TemplateExtension::PARAM_TEMPLATE_PATHS => [
                $this->workspace()->path('/')
            ],
            MaestroExtension::PARAM_NAMESPACE => self::EXAMPLE_NAMESPACE,
        ]);
    }

    protected function packageWorkspacePath(string $subPath = ''): string
    {
        $paths = [];
        if ($subPath) {
            $paths[] = self::EXAMPLE_NAMESPACE;
            $paths[] = $subPath;
        }
        return $this->workspace()->path(Path::join($paths));
    }
}
