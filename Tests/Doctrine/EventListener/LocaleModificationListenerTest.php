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

namespace Tests\Sylius\Bundle\LocaleBundle\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\LocaleBundle\Doctrine\EventListener\LocaleModificationListener;
use Symfony\Contracts\Cache\CacheInterface;

final class LocaleModificationListenerTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject
     */
    private MockObject $cacheMock;
    private LocaleModificationListener $localeModificationListener;
    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->localeModificationListener = new LocaleModificationListener($this->cacheMock);
    }

    public function testInvalidatesCache(): void
    {
        $this->cacheMock->expects($this->once())->method('delete')->with('sylius_locales');
        $this->localeModificationListener->invalidateCachedLocales();
    }
}
