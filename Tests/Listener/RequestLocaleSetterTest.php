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

namespace Tests\Sylius\Bundle\LocaleBundle\Listener;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\LocaleBundle\Listener\RequestLocaleSetter;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestLocaleSetterTest extends TestCase
{
    /**
     * @var LocaleContextInterface|MockObject
     */
    private MockObject $localeContextMock;
    /**
     * @var LocaleProviderInterface|MockObject
     */
    private MockObject $localeProviderMock;
    private RequestLocaleSetter $requestLocaleSetter;
    protected function setUp(): void
    {
        $this->localeContextMock = $this->createMock(LocaleContextInterface::class);
        $this->localeProviderMock = $this->createMock(LocaleProviderInterface::class);
        $this->requestLocaleSetter = new RequestLocaleSetter($this->localeContextMock, $this->localeProviderMock);
    }

    public function testSetsLocaleAndDefaultLocaleOnRequest(): void
    {
        /** @var RequestEvent|MockObject $eventMock */
        $eventMock = $this->createMock(RequestEvent::class);
        /** @var Request|MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);
        $eventMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $this->localeContextMock->expects($this->once())->method('getLocaleCode')->willReturn('pl_PL');
        $this->localeProviderMock->expects($this->once())->method('getDefaultLocaleCode')->willReturn('en_US');
        $requestMock->expects($this->once())->method('setLocale')->with('pl_PL');
        $requestMock->expects($this->once())->method('setDefaultLocale')->with('en_US');
        $this->requestLocaleSetter->onKernelRequest($eventMock);
    }
}
