<?php

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Guzzle\ServerMock6;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @group guzzle
 */
class ServerMock6Test extends TestCase
{
    /**
     * @var ServerMock6
     */
    private $server;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, 6, '<') || version_compare($guzzleVersion, 7, '>=')) {
            self::markTestSkipped(sprintf('Incompatible Guzzle version (%s)', $guzzleVersion));
        }

        $this->server = new ServerMock6();
        $this->client = new Client(['handler' => $this->server->getHandler()]);
    }

    /**
     * @group guzzle6
     */
    public function test_it_throws_mismatch_if_request_not_match()
    {
        $request = $this->createMock(PactRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException(new MismatchCollection([]));

        $pact = $this->createMock(PactInterface::class);
        $pact
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->server->handlePact($pact);

        self::expectException(Mismatch::class);

        $this->client->request('GET', '/');
    }

    /**
     * @group guzzle6
     */
    public function test_it_match_request_and_respond_with_a_response_mock()
    {
        $request = $this->createMock(PactRequestInterface::class);
        $response = $this->createMock(PactResponseInterface::class);

        $pact = $this->createMock(PactInterface::class);
        $pact
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);
        $pact
            ->expects(self::atLeastOnce())
            ->method('getResponse')
            ->willReturn($response);

        $request
            ->expects(self::once())
            ->method('assertMatch');

        $response
            ->expects(self::once())
            ->method('assertMatch');

        $response
            ->expects(self::once())
            ->method('getSample')
            ->willReturn($psr7Response = $this->createMock(ResponseInterface::class));

        $this->server->handlePact($pact);

        $resp = $this->client->request('GET', '/');
        self::assertSame($psr7Response, $resp);
    }
}
