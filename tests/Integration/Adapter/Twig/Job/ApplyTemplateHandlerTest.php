<?php

namespace Maestro\Tests\Integration\Adapter\Twig\Job;

use Maestro\Adapter\Twig\Job\ApplyTemplate;
use Maestro\Adapter\Twig\Job\ApplyTemplateHandler;
use Maestro\MaestroExtension;
use Maestro\Model\Package\Instantiator;
use Maestro\Model\Package\ManifestItem;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitionBuilder;
use Maestro\Tests\Integration\IntegrationTestCase;

class ApplyTemplateHandlerTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testRendersAndSavesTemplateToNewFile()
    {
        $this->workspace()->put('/test_template', 'Hello World');
        $definition = PackageDefinitionBuilder::create('foo/bar')->build();

        $this->handler()->__invoke(
            $this->createJob($definition, 'test_template')
        );

        self::assertFileExists($this->packageWorkspacePath('foo-bar/test_template'));
    }

    public function testCreatesTemplateAtNonExistingDirectory()
    {
        $this->workspace()->put('/sub-path/test_template', 'Hello World');
        $definition = PackageDefinitionBuilder::create('foo/bar')->build();

        $this->handler()->__invoke(
            $this->createJob($definition, 'sub-path/test_template')
        );

        self::assertFileExists($this->packageWorkspacePath('foo-bar/sub-path/test_template'));
    }

    public function testCreatesTemplateAtSpecifiedTargetPath()
    {
        $this->workspace()->put('test_template.twig', 'Hello World');
        $definition = PackageDefinitionBuilder::create('foo/bar')->build();

        ;
        $this->handler()->__invoke(
            $this->createJob($definition, 'test_template.twig', 'hello_world')
        );

        self::assertFileExists($this->packageWorkspacePath('foo-bar/hello_world'));
    }

    private function handler(): ApplyTemplateHandler
    {
        $handler = $this->container()->get(MaestroExtension::SERVICE_APPLY_TEMPLATE_HANDLER);
        return $handler;
    }

    private function createJob(PackageDefinition $definition, string $sourcePath, string $targetPath = null): ApplyTemplate
    {
        $targetPath = $targetPath ?: $sourcePath;
        $job = new ApplyTemplate(
            $definition,
            Instantiator::create()->instantiate(
                ManifestItem::class,
                [
                    'name' => $sourcePath,
                    'dest' => $targetPath
                ]
            )
        );
        return $job;
    }
}
