<?php

namespace App\Tests\Repository;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class ConfigurationRepositoryTest extends TestCase
{
    private ConfigurationRepository $repository;
    private EntityManager $mockEntityManager;
    private ManagerRegistry $mockManagerRegistry;

    protected function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManager::class);
        $this->mockManagerRegistry = $this->createMock(ManagerRegistry::class);
        
        $this->mockManagerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Configuration::class)
            ->willReturn($this->mockEntityManager);

        $this->repository = new ConfigurationRepository($this->mockManagerRegistry);
    }

    public function testGetValueReturnsValueWhenFound(): void
    {
        $configuration = new Configuration();
        $configuration->setConfigKey('test.key');
        $configuration->setConfigValue('test value');

        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findOneBy')
            ->with(['configKey' => 'test.key'])
            ->willReturn($configuration);

        $result = $this->repository->getValue('test.key');
        
        $this->assertEquals('test value', $result);
    }

    public function testGetValueReturnsNullWhenNotFound(): void
    {
        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findOneBy')
            ->with(['configKey' => 'nonexistent.key'])
            ->willReturn(null);

        $result = $this->repository->getValue('nonexistent.key');
        
        $this->assertNull($result);
    }

    public function testSetValueCreatesNewConfigurationWhenNotExists(): void
    {
        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findOneBy')
            ->with(['configKey' => 'new.key'])
            ->willReturn(null);

        $this->mockEntityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Configuration $config) {
                return $config->getConfigKey() === 'new.key' && 
                       $config->getConfigValue() === 'new value';
            }));

        $this->mockEntityManager->expects($this->once())
            ->method('flush');

        $this->repository->setValue('new.key', 'new value');
    }

    public function testSetValueUpdatesExistingConfiguration(): void
    {
        $existingConfig = new Configuration();
        $existingConfig->setConfigKey('existing.key');
        $existingConfig->setConfigValue('old value');

        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findOneBy')
            ->with(['configKey' => 'existing.key'])
            ->willReturn($existingConfig);

        $this->mockEntityManager->expects($this->once())
            ->method('flush');

        $this->repository->setValue('existing.key', 'updated value');
        
        $this->assertEquals('updated value', $existingConfig->getConfigValue());
    }

    public function testFindByCategoryReturnsFilteredResults(): void
    {
        $config1 = new Configuration();
        $config1->setConfigKey('shop.name');
        $config1->setCategory('shop');

        $config2 = new Configuration();
        $config2->setConfigKey('system.debug');
        $config2->setCategory('system');

        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findBy')
            ->with(['category' => 'shop'])
            ->willReturn([$config1]);

        $result = $this->repository->findByCategory('shop');
        
        $this->assertCount(1, $result);
        $this->assertEquals('shop.name', $result[0]->getConfigKey());
    }

    public function testFindPublicReturnsPublicConfigurations(): void
    {
        $publicConfig = new Configuration();
        $publicConfig->setConfigKey('public.key');
        $publicConfig->setPublic(true);

        $privateConfig = new Configuration();
        $privateConfig->setConfigKey('private.key');
        $privateConfig->setPublic(false);

        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findBy')
            ->with(['isPublic' => true])
            ->willReturn([$publicConfig]);

        $result = $this->repository->findPublic();
        
        $this->assertCount(1, $result);
        $this->assertEquals('public.key', $result[0]->getConfigKey());
    }

    public function testFindEditableReturnsEditableConfigurations(): void
    {
        $editableConfig = new Configuration();
        $editableConfig->setConfigKey('editable.key');
        $editableConfig->setEditable(true);

        $nonEditableConfig = new Configuration();
        $nonEditableConfig->setConfigKey('non-editable.key');
        $nonEditableConfig->setEditable(false);

        $this->mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(Configuration::class)
            ->willReturnSelf();

        $this->mockEntityManager->expects($this->once())
            ->method('findBy')
            ->with(['isEditable' => true])
            ->willReturn([$editableConfig]);

        $result = $this->repository->findEditable();
        
        $this->assertCount(1, $result);
        $this->assertEquals('editable.key', $result[0]->getConfigKey());
    }
} 