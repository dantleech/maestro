<?php

namespace Maestro\Library\Report;

use Amp\ByteStream\OutputStream;

interface ReportGenerator
{
    public function generate(OutputStream $outputStream): void;
}
