<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Tests\Unit\Middleware;

use Moselwal\FA4T3\Middleware\TrackingScriptMiddleware;
use Moselwal\FA4T3\Service\ConfigurationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class TrackingScriptMiddlewareTest extends TestCase
{
    private function buildRequest(?SiteInterface $site): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            static fn (string $name) => $name === 'site' ? $site : null,
        );

        return $request;
    }

    private function htmlResponse(string $body, string $contentType = 'text/html; charset=utf-8'): ResponseInterface
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write($body);
        $stream->rewind();

        return (new Response())
            ->withHeader('Content-Type', $contentType)
            ->withBody($stream);
    }

    /**
     * @param array<string, mixed> $trackingConfig
     */
    private function configService(array $trackingConfig, string $siteId): ConfigurationService
    {
        $configService = $this->createMock(ConfigurationService::class);
        $configService->method('getTrackingConfig')->willReturn($trackingConfig);
        $configService->method('getSiteId')->willReturn($siteId);

        return $configService;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function trackingConfig(array $overrides = []): array
    {
        return array_merge([
            'enabled' => true,
            'customDomain' => '',
            'excludedPages' => '',
            'consentCategory' => '',
            'spaMode' => '',
            'honorDnt' => false,
        ], $overrides);
    }

    private function handlerReturning(ResponseInterface $response): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        return $handler;
    }

    #[Test]
    public function emptyConsentCategoryInjectsUngatedExecutableScript(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $request = $this->buildRequest($site);
        $handler = $this->handlerReturning($this->htmlResponse('<html><body><p>Hi</p></body></html>'));

        $middleware = new TrackingScriptMiddleware(
            $this->configService($this->trackingConfig(['consentCategory' => '']), 'ABCDEF'),
        );

        $html = (string)$middleware->process($request, $handler)->getBody();

        self::assertStringContainsString('data-consent="ignore"', $html);
        self::assertStringContainsString('src="https://cdn.usefathom.com/script.js"', $html);
        self::assertStringContainsString('data-site="ABCDEF"', $html);
        self::assertStringNotContainsString('type="text/plain"', $html);
    }

    #[Test]
    public function nonEmptyConsentCategoryInjectsAuthorGatedScript(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $request = $this->buildRequest($site);
        $handler = $this->handlerReturning($this->htmlResponse('<html><body><p>Hi</p></body></html>'));

        $middleware = new TrackingScriptMiddleware(
            $this->configService($this->trackingConfig(['consentCategory' => 'analytics']), 'ABCDEF'),
        );

        $html = (string)$middleware->process($request, $handler)->getBody();

        self::assertStringContainsString('type="text/plain"', $html);
        self::assertStringContainsString('data-category="analytics"', $html);
        self::assertStringContainsString('data-src="https://cdn.usefathom.com/script.js"', $html);
        self::assertStringContainsString('data-site="ABCDEF"', $html);
        // No executable src attribute (the URL lives in data-src instead).
        self::assertStringNotContainsString(' src="', $html);
    }

    #[Test]
    public function spaAndDntAttributesArePreservedInBothBranches(): void
    {
        $site = $this->createMock(SiteInterface::class);

        foreach (['', 'analytics'] as $category) {
            $request = $this->buildRequest($site);
            $handler = $this->handlerReturning($this->htmlResponse('<html><body></body></html>'));

            $middleware = new TrackingScriptMiddleware(
                $this->configService(
                    $this->trackingConfig([
                        'consentCategory' => $category,
                        'spaMode' => 'auto',
                        'honorDnt' => true,
                    ]),
                    'ABCDEF',
                ),
            );

            $html = (string)$middleware->process($request, $handler)->getBody();

            self::assertStringContainsString('data-spa="auto"', $html);
            self::assertStringContainsString('data-honor-dnt="true"', $html);
        }
    }

    #[Test]
    public function noScriptInjectedWhenTrackingDisabled(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $request = $this->buildRequest($site);
        $handler = $this->handlerReturning($this->htmlResponse('<html><body></body></html>'));

        $middleware = new TrackingScriptMiddleware(
            $this->configService($this->trackingConfig(['enabled' => false]), 'ABCDEF'),
        );

        $html = (string)$middleware->process($request, $handler)->getBody();

        self::assertStringNotContainsString('<script', $html);
    }

    #[Test]
    public function noScriptInjectedWhenSiteIdEmpty(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $request = $this->buildRequest($site);
        $handler = $this->handlerReturning($this->htmlResponse('<html><body></body></html>'));

        $middleware = new TrackingScriptMiddleware(
            $this->configService($this->trackingConfig(), ''),
        );

        $html = (string)$middleware->process($request, $handler)->getBody();

        self::assertStringNotContainsString('<script', $html);
    }

    #[Test]
    public function noScriptInjectedForNonHtmlContentType(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $request = $this->buildRequest($site);
        $handler = $this->handlerReturning(
            $this->htmlResponse('{"foo":"bar"}', 'application/json'),
        );

        $middleware = new TrackingScriptMiddleware(
            $this->configService($this->trackingConfig(), 'ABCDEF'),
        );

        $html = (string)$middleware->process($request, $handler)->getBody();

        self::assertStringNotContainsString('<script', $html);
    }
}
