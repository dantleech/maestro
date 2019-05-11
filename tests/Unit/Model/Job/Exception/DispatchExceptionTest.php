<?php

namespace Maestro\Tests\Unit\Model\Job\Exception;

use Amp\MultiReasonException;
use Exception;
use Maestro\Model\Job\Exception\DispatchException;
use PHPUnit\Framework\TestCase;

class DispatchExceptionTest extends TestCase
{
    public function testCreateFromMultiReasonException()
    {
        $reasons = new MultiReasonException([
            new Exception('one'),
            new Exception('two')
        ]);
        $exception = DispatchException::fromException($reasons);
        $this->assertEquals('Dispatch failed: "one", "two"', $exception->getMessage());
    }

    public function testCreateFromException()
    {
        $reason = new Exception('one');
        $exception = DispatchException::fromException($reason);
        $this->assertEquals('Dispatch failed: "one"', $exception->getMessage());
    }
}
