<?php

namespace App\Tests\Service;

use App\Service\ConfigurationService;
use App\Repository\ConfigurationRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ConfigurationServiceTest extends TestCase
{
    private ConfigurationService $configurationService;
    private ConfigurationRepository $mockRepository;
    private FilesystemAdapter $mockCache;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(ConfigurationRepository::class);
        $this->mockCache = $this->createMock(FilesystemAdapter::class);
        
        $this->configurationService = new ConfigurationService(
            $this->mockRepository,
            $this->mockCache
        );
    }

    public function testGetShopNameReturnsDefaultWhenNotSet(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.name')
            ->willReturn(null);

        $result = $this->configurationService->getShopName();
        
        $this->assertEquals('SymfoShop', $result);
    }

    public function testGetShopNameReturnsConfiguredValue(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.name')
            ->willReturn('My Shop');

        $result = $this->configurationService->getShopName();
        
        $this->assertEquals('My Shop', $result);
    }

    public function testGetShopDescriptionReturnsDefaultWhenNotSet(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.description')
            ->willReturn(null);

        $result = $this->configurationService->getShopDescription();
        
        $this->assertEquals('Your trusted online shopping destination.', $result);
    }

    public function testGetShopEmailReturnsDefaultWhenNotSet(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.email')
            ->willReturn(null);

        $result = $this->configurationService->getShopEmail();
        
        $this->assertEquals('info@symfoshop.com', $result);
    }

    public function testGetShopPhoneReturnsDefaultWhenNotSet(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.phone')
            ->willReturn(null);

        $result = $this->configurationService->getShopPhone();
        
        $this->assertEquals('+1-555-0123', $result);
    }

    public function testGetCurrencyReturnsDefaultWhenNotSet(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.currency')
            ->willReturn(null);

        $result = $this->configurationService->getCurrency();
        
        $this->assertEquals('USD', $result);
    }

    public function testGetCurrencySymbolReturnsDefaultWhenNotSet(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('getValue')
            ->with('shop.currency_symbol')
            ->willReturn(null);

        $result = $this->configurationService->getCurrencySymbol();
        
        $this->assertEquals('$', $result);
    }

    public function testSetSavesValueAndClearsCache(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('setValue')
            ->with('shop.name', 'New Value');

        $this->mockCache->expects($this->once())
            ->method('clear');

        $this->configurationService->set('shop.name', 'New Value');
    }

    public function testClearCache(): void
    {
        $this->mockCache->expects($this->once())
            ->method('clear');

        $this->configurationService->clearCache();
    }
} 