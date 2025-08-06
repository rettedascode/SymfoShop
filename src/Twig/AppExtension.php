<?php

namespace App\Twig;

use App\Service\ConfigurationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private ConfigurationService $configurationService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('shop_name', [$this, 'getShopName']),
            new TwigFunction('shop_description', [$this, 'getShopDescription']),
            new TwigFunction('shop_email', [$this, 'getShopEmail']),
            new TwigFunction('shop_phone', [$this, 'getShopPhone']),
            new TwigFunction('currency', [$this, 'getCurrency']),
            new TwigFunction('currency_symbol', [$this, 'getCurrencySymbol']),
            new TwigFunction('config', [$this, 'getConfig']),
            new TwigFunction('config_get', [$this, 'getConfig']),
        ];
    }

    public function getShopName(): string
    {
        return $this->configurationService->getShopName();
    }

    public function getShopDescription(): string
    {
        return $this->configurationService->getShopDescription();
    }

    public function getShopEmail(): string
    {
        return $this->configurationService->getShopEmail();
    }

    public function getShopPhone(): string
    {
        return $this->configurationService->getShopPhone();
    }

    public function getCurrency(): string
    {
        return $this->configurationService->getCurrency();
    }

    public function getCurrencySymbol(): string
    {
        return $this->configurationService->getCurrencySymbol();
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->configurationService->get($key, $default);
    }
} 