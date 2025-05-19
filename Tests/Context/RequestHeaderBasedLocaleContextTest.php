<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Bundle\LocaleBundle\Context;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\LocaleBundle\Context\RequestHeaderBasedLocaleContext;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestHeaderBasedLocaleContextTest extends TestCase
{
    /**
     * @var RequestStack|MockObject
     */
    private MockObject $requestStackMock;
    /**
     * @var LocaleProviderInterface|MockObject
     */
    private MockObject $localeProviderMock;
    private RequestHeaderBasedLocaleContext $requestHeaderBasedLocaleContext;
    protected function setUp(): void
    {
        $this->requestStackMock = $this->createMock(RequestStack::class);
        $this->localeProviderMock = $this->createMock(LocaleProviderInterface::class);
        $this->requestHeaderBasedLocaleContext = new RequestHeaderBasedLocaleContext($this->requestStackMock, $this->localeProviderMock);
    }

    public function testALocaleContext(): void
    {
        $this->assertInstanceOf(LocaleContextInterface::class, $this->requestHeaderBasedLocaleContext);
    }

    public function testThrowsLocaleNotFoundExceptionIfMainRequestIsNotFound(): void
    {
        $this->requestStackMock->expects($this->once())->method('getMainRequest')->willReturn(null);
        $this->expectException(LocaleNotFoundException::class);
        $this->requestHeaderBasedLocaleContext->getLocaleCode();
    }

    public function testThrowsLocaleNotFoundExceptionIfLocaleFromMainRequestPreferredLanguageCannotBeResolved(): void
    {
        $request = new Request();
        $request->headers->set('Accept-Language', 'fr_FR');
        $this->requestStackMock->expects($this->once())->method('getMainRequest')->willReturn($request);
        $this->localeProviderMock->expects($this->once())->method('getDefaultLocaleCode')->willReturn('pl_PL');
        $this->localeProviderMock->expects($this->once())->method('getAvailableLocalesCodes')->willReturn(['pl_PL', 'de_DE']);
        $this->expectException(LocaleNotFoundException::class);
        $this->requestHeaderBasedLocaleContext->getLocaleCode();
    }

    public function testResolvesLocaleFromMainRequestPreferredLanguageInLocaleSyntax(): void
    {
        $request = new Request();
        $request->headers->set('Accept-Language', 'de_DE');
        $this->requestStackMock->expects($this->once())->method('getMainRequest')->willReturn($request);
        $this->localeProviderMock->expects($this->once())->method('getDefaultLocaleCode')->willReturn('pl_PL');
        $this->localeProviderMock->expects($this->once())->method('getAvailableLocalesCodes')->willReturn(['pl_PL', 'de_DE']);
        $this->assertSame('de_DE', $this->requestHeaderBasedLocaleContext->getLocaleCode());
    }

    public function testResolvesLocaleFromMainRequestPreferredLanguageInMixedCasedLanguageSyntax(): void
    {
        $request = new Request();
        $request->headers->set('Accept-Language', 'dE-De');
        $this->requestStackMock->expects($this->once())->method('getMainRequest')->willReturn($request);
        $this->localeProviderMock->expects($this->once())->method('getDefaultLocaleCode')->willReturn('pl_PL');
        $this->localeProviderMock->expects($this->once())->method('getAvailableLocalesCodes')->willReturn(['pl_PL', 'de_DE']);
        $this->assertSame('de_DE', $this->requestHeaderBasedLocaleContext->getLocaleCode());
    }

    public function testResolvesLocaleFromMainRequestPreferredLanguageInUpperCasedLanguageSyntax(): void
    {
        $request = new Request();
        $request->headers->set('Accept-Language', 'DE-DE');
        $this->requestStackMock->expects($this->once())->method('getMainRequest')->willReturn($request);
        $this->localeProviderMock->expects($this->once())->method('getDefaultLocaleCode')->willReturn('pl_PL');
        $this->localeProviderMock->expects($this->once())->method('getAvailableLocalesCodes')->willReturn(['pl_PL', 'de_DE']);
        $this->assertSame('de_DE', $this->requestHeaderBasedLocaleContext->getLocaleCode());
    }

    public function testResolvesLocaleFromMainRequestPreferredLanguageInLowerCasedLanguageSyntax(): void
    {
        $request = new Request();
        $request->headers->set('Accept-Language', 'de-de');
        $this->requestStackMock->expects($this->once())->method('getMainRequest')->willReturn($request);
        $this->localeProviderMock->expects($this->once())->method('getDefaultLocaleCode')->willReturn('pl_PL');
        $this->localeProviderMock->expects($this->once())->method('getAvailableLocalesCodes')->willReturn(['pl_PL', 'de_DE']);
        $this->assertSame('de_DE', $this->requestHeaderBasedLocaleContext->getLocaleCode());
    }
}
