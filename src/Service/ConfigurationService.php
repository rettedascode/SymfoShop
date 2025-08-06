<?php

namespace App\Service;

use App\Repository\ConfigurationRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ConfigurationService
{
    private const CACHE_KEY = 'shop_configuration';
    private const CACHE_TTL = 3600; // 1 hour

    private array $cache = [];
    private bool $cacheLoaded = false;

    public function __construct(
        private ConfigurationRepository $configurationRepository,
        private ?CacheItemPoolInterface $cachePool = null
    ) {
        if (!$this->cachePool) {
            $this->cachePool = new FilesystemAdapter();
        }
    }

    /**
     * Get a configuration value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadCache();
        return $this->cache[$key] ?? $default;
    }

    /**
     * Set a configuration value
     */
    public function set(string $key, mixed $value, string $dataType = 'string', ?string $description = null, ?string $category = null): void
    {
        $this->configurationRepository->setValue($key, $value, $dataType, $description, $category);
        $this->clearCache();
    }

    /**
     * Get all configuration values
     */
    public function getAll(): array
    {
        $this->loadCache();
        return $this->cache;
    }

    /**
     * Get configuration by category
     */
    public function getByCategory(string $category): array
    {
        $configs = $this->configurationRepository->getByCategory($category);
        $values = [];

        foreach ($configs as $config) {
            $values[$config->getConfigKey()] = $config->getTypedValue();
        }

        return $values;
    }

    /**
     * Get public configuration values only
     */
    public function getPublic(): array
    {
        return $this->configurationRepository->getPublicValues();
    }

    /**
     * Check if a configuration key exists
     */
    public function has(string $key): bool
    {
        $this->loadCache();
        return isset($this->cache[$key]);
    }

    /**
     * Clear the configuration cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
        $this->cacheLoaded = false;
        $this->cachePool->deleteItem(self::CACHE_KEY);
    }

    /**
     * Load configuration from cache or database
     */
    private function loadCache(): void
    {
        if ($this->cacheLoaded) {
            return;
        }

        $cacheItem = $this->cachePool->getItem(self::CACHE_KEY);
        
        if ($cacheItem->isHit()) {
            $this->cache = $cacheItem->get();
            $this->cacheLoaded = true;
            return;
        }

        $this->cache = $this->configurationRepository->getAllValues();
        $this->cacheLoaded = true;

        $cacheItem->set($this->cache);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cachePool->save($cacheItem);
    }

    /**
     * Initialize default configuration values
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            'shop.name' => [
                'value' => 'SymfoShop',
                'type' => 'string',
                'description' => 'The name of your shop',
                'category' => 'shop'
            ],
            'shop.description' => [
                'value' => 'Your one-stop shop for everything you need',
                'type' => 'text',
                'description' => 'Shop description',
                'category' => 'shop'
            ],
            'shop.email' => [
                'value' => 'info@symfoshop.com',
                'type' => 'string',
                'description' => 'Contact email address',
                'category' => 'shop'
            ],
            'shop.phone' => [
                'value' => '+1-555-0123',
                'type' => 'string',
                'description' => 'Contact phone number',
                'category' => 'shop'
            ],
            'shop.currency' => [
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency',
                'category' => 'shop'
            ],
            'shop.currency_symbol' => [
                'value' => '$',
                'type' => 'string',
                'description' => 'Currency symbol',
                'category' => 'shop'
            ],
            'products.per_page' => [
                'value' => 12,
                'type' => 'integer',
                'description' => 'Number of products per page',
                'category' => 'products'
            ],
            'products.featured_count' => [
                'value' => 6,
                'type' => 'integer',
                'description' => 'Number of featured products to display',
                'category' => 'products'
            ],
            'orders.allow_guest_checkout' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Allow customers to checkout without registration',
                'category' => 'orders'
            ],
            'orders.free_shipping_threshold' => [
                'value' => 50.00,
                'type' => 'string',
                'description' => 'Order amount for free shipping',
                'category' => 'orders'
            ],
            'theme.primary_color' => [
                'value' => '#007bff',
                'type' => 'string',
                'description' => 'Primary theme color',
                'category' => 'theme'
            ],
            'theme.sidebar_collapsed' => [
                'value' => false,
                'type' => 'boolean',
                'description' => 'Start with sidebar collapsed',
                'category' => 'theme'
            ]
        ];

        foreach ($defaults as $key => $config) {
            if (!$this->has($key)) {
                $this->set(
                    $key,
                    $config['value'],
                    $config['type'],
                    $config['description'],
                    $config['category']
                );
            }
        }
    }

    /**
     * Get shop-specific configuration methods
     */
    public function getShopName(): string
    {
        return $this->get('shop.name') ?? 'SymfoShop';
    }

    public function getShopDescription(): string
    {
        return $this->get('shop.description') ?? 'Your trusted online shopping destination.';
    }

    public function getShopEmail(): string
    {
        return $this->get('shop.email') ?? 'info@symfoshop.com';
    }

    public function getShopPhone(): string
    {
        return $this->get('shop.phone') ?? '+1-555-0123';
    }

    public function getCurrency(): string
    {
        return $this->get('shop.currency') ?? 'USD';
    }

    public function getCurrencySymbol(): string
    {
        return $this->get('shop.currency_symbol') ?? '$';
    }
} 