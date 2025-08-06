<?php

namespace App\Tests\Entity;

use App\Entity\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->configuration->getId());
        $this->assertNull($this->configuration->getConfigKey());
        $this->assertNull($this->configuration->getConfigValue());
        $this->assertNull($this->configuration->getDescription());
        $this->assertEquals('string', $this->configuration->getDataType());
        $this->assertEquals('system', $this->configuration->getCategory());
        $this->assertTrue($this->configuration->isEditable());
        $this->assertTrue($this->configuration->isPublic());
        $this->assertNotNull($this->configuration->getCreatedAt());
        $this->assertNotNull($this->configuration->getUpdatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $this->configuration->setConfigKey('test.key');
        $this->configuration->setConfigValue('test value');
        $this->configuration->setDescription('Test description');
        $this->configuration->setDataType('integer');
        $this->configuration->setCategory('shop');
        $this->configuration->setEditable(false);
        $this->configuration->setPublic(false);

        $this->assertEquals('test.key', $this->configuration->getConfigKey());
        $this->assertEquals('test value', $this->configuration->getConfigValue());
        $this->assertEquals('Test description', $this->configuration->getDescription());
        $this->assertEquals('integer', $this->configuration->getDataType());
        $this->assertEquals('shop', $this->configuration->getCategory());
        $this->assertFalse($this->configuration->isEditable());
        $this->assertFalse($this->configuration->isPublic());
    }

    public function testGetTypedValueForString(): void
    {
        $this->configuration->setDataType('string');
        $this->configuration->setConfigValue('test string');

        $result = $this->configuration->getTypedValue();
        $this->assertEquals('test string', $result);
        $this->assertIsString($result);
    }

    public function testGetTypedValueForInteger(): void
    {
        $this->configuration->setDataType('integer');
        $this->configuration->setConfigValue('123');

        $result = $this->configuration->getTypedValue();
        $this->assertEquals(123, $result);
        $this->assertIsInt($result);
    }

    public function testGetTypedValueForBoolean(): void
    {
        $this->configuration->setDataType('boolean');
        $this->configuration->setConfigValue('true');

        $result = $this->configuration->getTypedValue();
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testGetTypedValueForJson(): void
    {
        $this->configuration->setDataType('json');
        $this->configuration->setConfigValue('{"key": "value"}');

        $result = $this->configuration->getTypedValue();
        $this->assertEquals(['key' => 'value'], $result);
        $this->assertIsArray($result);
    }

    public function testGetTypedValueForInvalidJson(): void
    {
        $this->configuration->setDataType('json');
        $this->configuration->setConfigValue('invalid json');

        $result = $this->configuration->getTypedValue();
        $this->assertEquals('invalid json', $result);
    }

    public function testGetTypedValueForUnknownType(): void
    {
        $this->configuration->setDataType('unknown');
        $this->configuration->setConfigValue('test value');

        $result = $this->configuration->getTypedValue();
        $this->assertEquals('test value', $result);
    }

    public function testSetTypedValueForString(): void
    {
        $this->configuration->setDataType('string');
        $this->configuration->setTypedValue('test string');

        $this->assertEquals('test string', $this->configuration->getConfigValue());
    }

    public function testSetTypedValueForInteger(): void
    {
        $this->configuration->setDataType('integer');
        $this->configuration->setTypedValue(123);

        $this->assertEquals('123', $this->configuration->getConfigValue());
    }

    public function testSetTypedValueForBoolean(): void
    {
        $this->configuration->setDataType('boolean');
        $this->configuration->setTypedValue(true);

        $this->assertEquals('true', $this->configuration->getConfigValue());
    }

    public function testSetTypedValueForArray(): void
    {
        $this->configuration->setDataType('json');
        $this->configuration->setTypedValue(['key' => 'value']);

        $this->assertEquals('{"key":"value"}', $this->configuration->getConfigValue());
    }

    public function testUpdateTimestamp(): void
    {
        $originalUpdatedAt = $this->configuration->getUpdatedAt();
        
        // Wait a moment to ensure timestamp difference
        usleep(1000);
        
        $this->configuration->updateTimestamp();
        
        $this->assertGreaterThan($originalUpdatedAt, $this->configuration->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        // Test valid configuration
        $this->configuration->setConfigKey('test.key');
        $this->configuration->setConfigValue('test value');
        $this->configuration->setDataType('string');

        $violations = $validator->validate($this->configuration);
        $this->assertCount(0, $violations);

        // Test invalid configuration (empty config key)
        $this->configuration->setConfigKey('');
        $violations = $validator->validate($this->configuration);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testToString(): void
    {
        $this->configuration->setConfigKey('test.key');
        $this->configuration->setConfigValue('test value');

        $this->assertEquals('test.key: test value', (string) $this->configuration);
    }
} 