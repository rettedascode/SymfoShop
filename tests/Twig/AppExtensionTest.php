<?php

namespace App\Tests\Twig;

use App\Service\ConfigurationService;
use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;

class AppExtensionTest extends TestCase
{
    private AppExtension $extension;
    private ConfigurationService $mockConfigurationService;

    protected function setUp(): void
    {
        $this->mockConfigurationService = $this->createMock(ConfigurationService::class);
        $this->extension = new AppExtension($this->mockConfigurationService);
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();
        
        $this->assertCount(5, $functions);
        
        $functionNames = array_map(fn($function) => $function->getName(), $functions);
        $this->assertContains('shop_name', $functionNames);
        $this->assertContains('shop_description', $functionNames);
        $this->assertContains('shop_email', $functionNames);
        $this->assertContains('shop_phone', $functionNames);
        $this->assertContains('currency_symbol', $functionNames);
    }

    public function testShopNameFunction(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('getShopName')
            ->willReturn('Test Shop');

        $result = $this->extension->getShopName();
        
        $this->assertEquals('Test Shop', $result);
    }

    public function testShopDescriptionFunction(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('getShopDescription')
            ->willReturn('Test Description');

        $result = $this->extension->getShopDescription();
        
        $this->assertEquals('Test Description', $result);
    }

    public function testShopEmailFunction(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('getShopEmail')
            ->willReturn('test@example.com');

        $result = $this->extension->getShopEmail();
        
        $this->assertEquals('test@example.com', $result);
    }

    public function testShopPhoneFunction(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('getShopPhone')
            ->willReturn('+1-555-0123');

        $result = $this->extension->getShopPhone();
        
        $this->assertEquals('+1-555-0123', $result);
    }

    public function testCurrencySymbolFunction(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('getCurrencySymbol')
            ->willReturn('$');

        $result = $this->extension->getCurrencySymbol();
        
        $this->assertEquals('$', $result);
    }
} 