<?php

namespace Phpactor\Extension\Maestro\Model;

class Console
{
    private $buffer = '';
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function write(string $buffer)
    {
        $this->buffer .= $buffer;
    }

    public function flush(): string
    {
        $out = $this->buffer;
        $this->buffer = '';
        return $out;
    }

    public function tail(int $nbLines): string
    {
        $buffer = $this->buffer;
        $lines = explode(PHP_EOL, $this->buffer);

        if (count($lines) < $nbLines) {
            return implode(PHP_EOL, $lines);
        }

        return implode(PHP_EOL, array_splice($lines, -$nbLines));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function writeln(string $string)
    {
        $this->buffer .= $string . PHP_EOL;
    }
}
