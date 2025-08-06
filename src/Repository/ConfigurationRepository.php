<?php

namespace App\Repository;

use App\Entity\Configuration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Configuration>
 *
 * @method Configuration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Configuration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Configuration[]    findAll()
 * @method Configuration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Configuration::class);
    }

    public function save(Configuration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Configuration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get a configuration value by key
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $config = $this->findOneBy(['configKey' => $key]);
        
        if (!$config) {
            return $default;
        }

        return $config->getTypedValue();
    }

    /**
     * Set a configuration value by key
     */
    public function setValue(string $key, mixed $value, string $dataType = 'string', ?string $description = null, ?string $category = null): Configuration
    {
        $config = $this->findOneBy(['configKey' => $key]);
        
        if (!$config) {
            $config = new Configuration();
            $config->setConfigKey($key);
            $config->setDataType($dataType);
            $config->setDescription($description);
            $config->setCategory($category);
        }

        $config->setTypedValue($value);
        $this->save($config, true);

        return $config;
    }

    /**
     * Get all configuration values as an associative array
     */
    public function getAllValues(): array
    {
        $configs = $this->findAll();
        $values = [];

        foreach ($configs as $config) {
            $values[$config->getConfigKey()] = $config->getTypedValue();
        }

        return $values;
    }

    /**
     * Get configuration values by category
     */
    public function getByCategory(string $category): array
    {
        return $this->findBy(['category' => $category], ['configKey' => 'ASC']);
    }

    /**
     * Get public configuration values only
     */
    public function getPublicValues(): array
    {
        $configs = $this->findBy(['isPublic' => true]);
        $values = [];

        foreach ($configs as $config) {
            $values[$config->getConfigKey()] = $config->getTypedValue();
        }

        return $values;
    }

    /**
     * Check if a configuration key exists
     */
    public function hasKey(string $key): bool
    {
        return $this->count(['configKey' => $key]) > 0;
    }

    /**
     * Get configuration by key with fallback to environment variable
     */
    public function getValueWithEnvFallback(string $key, string $envKey, mixed $default = null): mixed
    {
        $value = $this->getValue($key);
        
        if ($value !== null) {
            return $value;
        }

        // Fallback to environment variable (only if it exists)
        $envValue = null;
        
        // Try to get from $_ENV first
        if (isset($_ENV[$envKey])) {
            $envValue = $_ENV[$envKey];
        }
        // Try to get from getenv() as fallback
        elseif (getenv($envKey) !== false) {
            $envValue = getenv($envKey);
        }
        
        if ($envValue !== null && $envValue !== '') {
            // Store the env value in database for future use
            $this->setValue($key, $envValue, 'string', "Auto-imported from environment variable: $envKey");
            return $envValue;
        }

        return $default;
    }
} 