<?php

namespace Maestro\Extension\HttpClient\Client;

use Amp\Artax\Client;
use Amp\Artax\Request;
use Amp\Artax\Response;
use Amp\CancellationToken;
use Amp\Promise;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LoggingClient implements Client
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $innerClient;

    public function __construct(Client $innerClient, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->innerClient = $innerClient;
    }

    /**
     * {@inheritDoc}
     */
    public function request($uriOrRequest, array $options = [
    ], CancellationToken $cancellation = null): Promise
    {
        $uri = $this->resolveUri($uriOrRequest);

        $this->logger->debug(sprintf('>> %s', $uri));
        return \Amp\call(function () use ($uri, $uriOrRequest, $options, $cancellation) {
            $response = yield $this->innerClient->request($uriOrRequest, $options, $cancellation);
            assert($response instanceof Response);
            $this->logger->debug(sprintf('<< %s %s', $uri, $response->getStatus()));
            return $response;
        });
    }

    private function resolveUri($uriOrRequest): string
    {
        if ($uriOrRequest instanceof Request) {
            return $uriOrRequest->getUri();
        }
        
        if (is_string($uriOrRequest)) {
            return $uriOrRequest;
        }

        throw new RuntimeException(sprintf(
            'Argument must either be a Amp\Artax\Request or a string, got "%s"',
            gettype($uriOrRequest)
        ));
    }
}
