<?php

namespace Maestro\Model\Job\Exception;

use Amp\MultiReasonException;
use Exception;
use RuntimeException;

class DispatchException extends RuntimeException
{
    public static function fromException(Exception $e)
    {
        if ($e instanceof MultiReasonException) {
            return new self(sprintf(
                'Dispatch failed: "%s"',
                implode('", "', array_map(function (Exception $e) {
                    return $e->getMessage();
                }, $e->getReasons()))
            ), 0, $e);
        }

        return new self(sprintf(
            'Dispatch failed: "%s"',
            $e->getMessage(),
            ), 0, $e);
    }
}
