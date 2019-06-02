<?php

namespace Maestro\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    private $workspace;

    public function workspace(): Workspace
    {
        if (!$this->workspace) {
            return $this->workspace = Workspace::create(__DIR__ . '/Workspace');
        }

        return $this->workspace;
    }

    protected function createPlan(string $name, array $data)
    {
        $this->workspace()->put($name, json_encode($data, JSON_PRETTY_PRINT));
    }
}
