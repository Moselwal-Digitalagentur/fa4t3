<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Tests\Unit\Service;

use GuzzleHttp\Psr7\Response;
use Moselwal\FA4T3\Domain\Model\AggregationRequest;
use Moselwal\FA4T3\Exception\Fa4t3ApiException;
use Moselwal\FA4T3\Exception\Fa4t3AuthenticationException;
use Moselwal\FA4T3\Exception\Fa4t3RateLimitException;
use Moselwal\FA4T3\Service\Fa4t3ApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Http\RequestFactory;

class Fa4t3ApiClientTest extends TestCase
{
    #[Test]
    public function testConnectionReturnsSuccessOnValidKey(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(200, [], '{"status":"authenticated"}')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $result = $client->testConnection('valid-key');

        self::assertTrue($result->isSuccess());
    }

    #[Test]
    public function testConnectionReturnsFailureOnInvalidKey(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(401, [], '{"error":"Invalid token"}')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $result = $client->testConnection('invalid-key');

        self::assertFalse($result->isSuccess());
        self::assertSame('Invalid API key', $result->getMessage());
    }

    #[Test]
    public function getAggregationParsesResponseCorrectly(): void
    {
        $responseBody = json_encode([
            ['visits' => 100, 'uniques' => 75, 'pageviews' => 200, 'avg_duration' => 45.5, 'bounce_rate' => 0.35]
        ]);

        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(200, [], $responseBody)
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $request = new AggregationRequest(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-15')
        );

        $result = $client->getAggregation('SITE123', $request, 'api-key');

        self::assertSame(100, $result->getVisits());
        self::assertSame(75, $result->getUniques());
        self::assertSame(200, $result->getPageviews());
        self::assertEqualsWithDelta(45.5, $result->getAvgDuration(), 0.01);
        self::assertEqualsWithDelta(0.35, $result->getBounceRate(), 0.01);
    }

    #[Test]
    public function getAggregationThrowsOnRateLimit(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(429, [], '{"error":"Rate limit exceeded"}')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $request = new AggregationRequest(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-15')
        );

        $this->expectException(Fa4t3RateLimitException::class);
        $client->getAggregation('SITE123', $request, 'api-key');
    }

    #[Test]
    public function getAggregationThrowsOnAuthenticationError(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(401, [], '{"error":"Invalid token"}')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $request = new AggregationRequest(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-15')
        );

        $this->expectException(Fa4t3AuthenticationException::class);
        $client->getAggregation('SITE123', $request, 'api-key');
    }

    #[Test]
    public function getAggregationThrowsOnServerError(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(500, [], '{"error":"Internal server error"}')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $request = new AggregationRequest(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-15')
        );

        $this->expectException(Fa4t3ApiException::class);
        $client->getAggregation('SITE123', $request, 'api-key');
    }

    #[Test]
    public function getCurrentVisitorsParsesSimpleResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(200, [], '{"total":42}')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $result = $client->getCurrentVisitors('SITE123', 'api-key');

        self::assertSame(42, $result->getTotal());
    }

    #[Test]
    public function getCurrentVisitorsParsesDetailedResponse(): void
    {
        $responseBody = json_encode([
            'total' => 42,
            'content' => [['pathname' => '/blog', 'total' => 10]],
            'referrers' => [['referrer_hostname' => 'google.com', 'total' => 5]],
        ]);

        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(200, [], $responseBody)
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $result = $client->getCurrentVisitors('SITE123', 'api-key', true);

        self::assertSame(42, $result->getTotal());
        self::assertCount(1, $result->getTopPages());
        self::assertCount(1, $result->getTopReferrers());
    }

    #[Test]
    public function getEventsReturnsEventList(): void
    {
        $responseBody = json_encode([
            'data' => [
                ['id' => 'evt1', 'name' => 'Newsletter Signup', 'created_at' => '2026-01-01T00:00:00Z'],
                ['id' => 'evt2', 'name' => 'Purchase', 'created_at' => '2026-02-01T00:00:00Z'],
            ],
            'has_more' => false,
        ]);

        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willReturn(
            new Response(200, [], $responseBody)
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $events = $client->getEvents('SITE123', 'api-key');

        self::assertCount(2, $events);
        self::assertSame('Newsletter Signup', $events[0]->getName());
        self::assertSame('Purchase', $events[1]->getName());
    }

    #[Test]
    public function getAggregationThrowsOnTimeout(): void
    {
        $requestFactory = $this->createMock(RequestFactory::class);
        $requestFactory->method('request')->willThrowException(
            new \RuntimeException('Connection timed out')
        );

        $client = new Fa4t3ApiClient($requestFactory);
        $request = new AggregationRequest(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-15')
        );

        $this->expectException(Fa4t3ApiException::class);
        $this->expectExceptionMessage('Fathom API request failed');
        $client->getAggregation('SITE123', $request, 'api-key');
    }
}
