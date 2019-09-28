<?php

namespace Maestro\Library\Vcs;

use Amp\Promise;
use Maestro\Library\Support\Environment\Environment;

interface Repository
{
    /**
     * @throws CheckoutError
     * @return Promise<void>
     */
    public function checkout(string $url, Environment $environment): Promise;

    /**
     * @return Promise<Tags>
     */
    public function listTags(): Promise;

    /**
     * @return Promise<void>
     */
    public function tag(string $name): Promise;

    /**
     * @return Promise<string>
     */
    public function headId(): Promise;

    /**
     * @return Promise<string[]>
     */
    public function commitsBetween(string $id1, string $id2): Promise;

    public function isCheckedOut(): bool;
}
