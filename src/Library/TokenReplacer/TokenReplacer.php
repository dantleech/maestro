<?php

namespace Maestro\Library\TokenReplacer;

use Maestro\Library\TokenReplacer\Exception\InvalidTokenType;
use Maestro\Library\TokenReplacer\Exception\UnknownToken;

class TokenReplacer
{
    /**
     * @var string
     */
    private $delimiter;

    public function __construct(string $delimiter = '%')
    {
        $this->delimiter = $delimiter;
    }

    public function replace(string $input, array $tokens)
    {
        preg_match_all(sprintf('{%s(.*?)%s}', $this->delimiter, $this->delimiter), $input, $matches);
        if (!count($matches[1])) {
            return $input;
        }

        return array_reduce($matches[1], function ($acc, string $match) use ($tokens) {
            if (!isset($tokens[$match])) {
                throw new UnknownToken(sprintf(
                    'Token "%s" not known, known tokens: "%s"',
                    $match,
                    implode('", "', array_keys($tokens))
                ));
            }

            return $this->resolveValue($acc, $match, $tokens[$match]);
        }, $input);
    }

    private function resolveValue($input, string $token, $value)
    {
        if (is_string($value)) {
            return str_replace($this->delimiter . $token . $this->delimiter, $value, $input);
        }

        if (is_array($value)) {
            return $value;
        }

        throw new InvalidTokenType(sprintf('Type "%s" is not supported for tokens (in token "%s")', gettype($value), $token));
    }
}
