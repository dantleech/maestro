<?php

namespace Maestro\Tests\Unit\Library\Support\Variables;

use Maestro\Library\Support\Variables\Variables;
use PHPUnit\Framework\TestCase;

class VariablesTest extends TestCase
{
    public function testMerge()
    {
        self::assertEquals(['foo', 'bar'], Variables::fromArray(['foo'])->merge(Variables::fromArray(['bar']))->toArray());
    }
}
