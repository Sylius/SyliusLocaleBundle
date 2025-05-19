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

namespace Tests\Sylius\Bundle\LocaleBundle\Checker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\LocaleBundle\Checker\LocaleUsageChecker;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Metadata\MetadataInterface;
use Sylius\Resource\Metadata\RegistryInterface;

final class LocaleUsageCheckerTest extends TestCase
{
    /** @var RepositoryInterface&MockObject */
    private MockObject $localeRepositoryMock;

    /** @var RegistryInterface&MockObject */
    private MockObject $registryMock;

    /** @var EntityManagerInterface&MockObject */
    private MockObject $entityManagerMock;

    private LocaleUsageChecker $localeUsageChecker;

    protected function setUp(): void
    {
        $this->localeRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->localeUsageChecker = new LocaleUsageChecker($this->localeRepositoryMock, $this->registryMock, $this->entityManagerMock);
    }

    public function testThrowsExceptionWhenLocaleWithProvidedLocaleCodeDoesntExist(): void
    {
        $this->localeRepositoryMock->expects($this->once())->method('findOneBy')->with(['code' => 'en_US'])->willReturn(null);
        $this->expectException(LocaleNotFoundException::class);
        $this->localeUsageChecker->isUsed('en_US');
    }

    public function testReturnsTrueWhenAtLeastOneUsageOfLocaleFound(): void
    {
        /** @var LocaleInterface&MockObject $localeMock */
        $localeMock = $this->createMock(LocaleInterface::class);
        /** @var MetadataInterface&MockObject $firstResourceMetadataMock */
        $firstResourceMetadataMock = $this->createMock(MetadataInterface::class);
        /** @var MetadataInterface&MockObject $secondResourceMetadataMock */
        $secondResourceMetadataMock = $this->createMock(MetadataInterface::class);
        /** @var UnitOfWork&MockObject $unitOfWorkMock */
        $unitOfWorkMock = $this->createMock(UnitOfWork::class);
        /** @var EntityPersister&MockObject $entityPersisterMock */
        $entityPersisterMock = $this->createMock(EntityPersister::class);
        $localeRepository = new EntityRepository($this->entityManagerMock, new ClassMetadata(LocaleInterface::class));
        $this->localeUsageChecker = new LocaleUsageChecker($localeRepository, $this->registryMock, $this->entityManagerMock);
        $this->entityManagerMock->expects($this->once())->method('getUnitOfWork')->willReturn($unitOfWorkMock);
        $unitOfWorkMock->expects($this->once())->method('getEntityPersister')->with(LocaleInterface::class)->willReturn($entityPersisterMock);
        $entityPersisterMock->expects($this->once())->method('load')->with(['code' => 'en_US'], null, null, [], null, 1, null)->willReturn($localeMock);
        $entityPersisterMock->expects($this->once())->method('count')->with(['locale' => 'en_US'])->willReturn(1);
        $this->registryMock->expects($this->once())->method('getAll')->willReturn([$firstResourceMetadataMock, $secondResourceMetadataMock]);
        $firstResourceMetadataMock->expects($this->once())->method('getParameters')->willReturn([
            'translation' => [
                'classes' => [
                    'interface' => LocaleInterface::class,
                ],
            ],
        ]);
        $secondResourceMetadataMock->expects($this->once())->method('getParameters')->willReturn([]);
        $this->entityManagerMock->expects($this->once())->method('getRepository')->with(LocaleInterface::class)->willReturn($localeRepository);
        $this->assertTrue($this->localeUsageChecker->isUsed('en_US'));
    }

    public function testReturnsFalseWhenNoUsageOfLocaleFound(): void
    {
        /** @var LocaleInterface&MockObject $localeMock */
        $localeMock = $this->createMock(LocaleInterface::class);
        /** @var MetadataInterface&MockObject $firstResourceMetadataMock */
        $firstResourceMetadataMock = $this->createMock(MetadataInterface::class);
        /** @var MetadataInterface&MockObject $secondResourceMetadataMock */
        $secondResourceMetadataMock = $this->createMock(MetadataInterface::class);
        /** @var UnitOfWork&MockObject $unitOfWorkMock */
        $unitOfWorkMock = $this->createMock(UnitOfWork::class);
        /** @var EntityPersister&MockObject $entityPersisterMock */
        $entityPersisterMock = $this->createMock(EntityPersister::class);
        $localeRepository = new EntityRepository($this->entityManagerMock, new ClassMetadata(LocaleInterface::class));
        $this->localeUsageChecker = new LocaleUsageChecker($localeRepository, $this->registryMock, $this->entityManagerMock);
        $this->entityManagerMock->expects($this->once())->method('getUnitOfWork')->willReturn($unitOfWorkMock);
        $unitOfWorkMock->expects($this->once())->method('getEntityPersister')->with(LocaleInterface::class)->willReturn($entityPersisterMock);
        $entityPersisterMock->expects($this->once())->method('load')->with(['code' => 'en_US'], null, null, [], null, 1, null)->willReturn($localeMock);
        $entityPersisterMock->expects($this->once())->method('count')->with(['locale' => 'en_US'])->willReturn(0);
        $this->registryMock->expects($this->once())->method('getAll')->willReturn([$firstResourceMetadataMock, $secondResourceMetadataMock]);
        $firstResourceMetadataMock->expects($this->once())->method('getParameters')->willReturn([
            'translation' => [
                'classes' => [
                    'interface' => LocaleInterface::class,
                ],
            ],
        ]);
        $secondResourceMetadataMock->expects($this->once())->method('getParameters')->willReturn([]);
        $this->entityManagerMock->expects($this->once())->method('getRepository')->with(LocaleInterface::class)->willReturn($localeRepository);
        $this->assertFalse($this->localeUsageChecker->isUsed('en_US'));
    }
}
