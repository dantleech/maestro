<?php

namespace Maestro\Tests\Integration\Extension\Tmux\Model;

use Maestro\Extension\Tmux\Model\Exception\TmuxFailure;
use Maestro\Extension\Tmux\Model\TmuxClient;
use Maestro\Tests\IntegrationTestCase;

/**
 * @group tmux
 */
class TmuxClientTest extends IntegrationTestCase
{
    /**
     * @var TmuxClient
     */
    private $client;

    protected function setUp(): void
    {
        $this->workspace()->reset();

        $this->client = new TmuxClient(
            $this->workspace()->path('tmuxsocket')
        );
    }

    public function testCreateSession()
    {
        $this->client->createSession('foobar', getcwd());
        try {
            $this->client->createSession('foobar', getcwd());
            $this->fail('Was able to create two sessions with same name');
        } catch (TmuxFailure $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function testListSessions()
    {
        $this->client->createSession('foobar', getcwd());
        $this->client->createSession('barfoo', getcwd());
        $sessions = $this->client->listSessions();
        $this->assertEquals(['barfoo', 'foobar'], $sessions);
    }
}
