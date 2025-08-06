<?php

namespace App\Tests\Entity;

use App\Entity\Address;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class AddressTest extends TestCase
{
    private Address $address;
    private User $user;

    protected function setUp(): void
    {
        $this->address = new Address();
        $this->user = new User();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->address->getId());
        $this->assertNull($this->address->getFirstName());
        $this->assertNull($this->address->getLastName());
        $this->assertNull($this->address->getCompany());
        $this->assertNull($this->address->getAddressLine1());
        $this->assertNull($this->address->getAddressLine2());
        $this->assertNull($this->address->getCity());
        $this->assertNull($this->address->getState());
        $this->assertNull($this->address->getPostalCode());
        $this->assertNull($this->address->getCountry());
        $this->assertNull($this->address->getPhone());
        $this->assertTrue($this->address->isDefault());
        $this->assertNull($this->address->getUser());
        $this->assertNotNull($this->address->getCreatedAt());
        $this->assertNotNull($this->address->getUpdatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $this->address->setFirstName('John');
        $this->address->setLastName('Doe');
        $this->address->setCompany('Test Company');
        $this->address->setAddressLine1('123 Main St');
        $this->address->setAddressLine2('Apt 4B');
        $this->address->setCity('New York');
        $this->address->setState('NY');
        $this->address->setPostalCode('10001');
        $this->address->setCountry('USA');
        $this->address->setPhone('+1-555-0123');
        $this->address->setIsDefault(false);
        $this->address->setUser($this->user);

        $this->assertEquals('John', $this->address->getFirstName());
        $this->assertEquals('Doe', $this->address->getLastName());
        $this->assertEquals('Test Company', $this->address->getCompany());
        $this->assertEquals('123 Main St', $this->address->getAddressLine1());
        $this->assertEquals('Apt 4B', $this->address->getAddressLine2());
        $this->assertEquals('New York', $this->address->getCity());
        $this->assertEquals('NY', $this->address->getState());
        $this->assertEquals('10001', $this->address->getPostalCode());
        $this->assertEquals('USA', $this->address->getCountry());
        $this->assertEquals('+1-555-0123', $this->address->getPhone());
        $this->assertFalse($this->address->isDefault());
        $this->assertEquals($this->user, $this->address->getUser());
    }

    public function testGetFullName(): void
    {
        $this->address->setFirstName('John');
        $this->address->setLastName('Doe');
        $this->assertEquals('John Doe', $this->address->getFullName());
    }

    public function testGetFullNameWithNullValues(): void
    {
        $this->assertEquals(' ', $this->address->getFullName());
    }

    public function testGetFullAddress(): void
    {
        $this->address->setAddressLine1('123 Main St');
        $this->address->setAddressLine2('Apt 4B');
        $this->address->setCity('New York');
        $this->address->setState('NY');
        $this->address->setPostalCode('10001');
        $this->address->setCountry('USA');

        $expected = "123 Main St\nApt 4B\nNew York, NY 10001\nUSA";
        $this->assertEquals($expected, $this->address->getFullAddress());
    }

    public function testGetFullAddressWithoutAddressLine2(): void
    {
        $this->address->setAddressLine1('123 Main St');
        $this->address->setCity('New York');
        $this->address->setState('NY');
        $this->address->setPostalCode('10001');
        $this->address->setCountry('USA');

        $expected = "123 Main St\nNew York, NY 10001\nUSA";
        $this->assertEquals($expected, $this->address->getFullAddress());
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->address->setCreatedAt($date);
        $this->assertEquals($date, $this->address->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->address->setUpdatedAt($date);
        $this->assertEquals($date, $this->address->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid address
        $this->address->setFirstName('John');
        $this->address->setLastName('Doe');
        $this->address->setAddressLine1('123 Main St');
        $this->address->setCity('New York');
        $this->address->setState('NY');
        $this->address->setPostalCode('10001');
        $this->address->setCountry('USA');

        $violations = $validator->validate($this->address);
        $this->assertCount(0, $violations);

        // Test blank first name
        $this->address->setFirstName('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));

        // Test blank last name
        $this->address->setFirstName('John');
        $this->address->setLastName('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));

        // Test blank address line 1
        $this->address->setLastName('Doe');
        $this->address->setAddressLine1('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));

        // Test blank city
        $this->address->setAddressLine1('123 Main St');
        $this->address->setCity('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));

        // Test blank state
        $this->address->setCity('New York');
        $this->address->setState('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));

        // Test blank postal code
        $this->address->setState('NY');
        $this->address->setPostalCode('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));

        // Test blank country
        $this->address->setPostalCode('10001');
        $this->address->setCountry('');
        $violations = $validator->validate($this->address);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testNullableFields(): void
    {
        $this->address->setCompany(null);
        $this->address->setAddressLine2(null);
        $this->address->setPhone(null);
        $this->address->setUser(null);

        $this->assertNull($this->address->getCompany());
        $this->assertNull($this->address->getAddressLine2());
        $this->assertNull($this->address->getPhone());
        $this->assertNull($this->address->getUser());
    }

    public function testDefaultStatus(): void
    {
        $this->assertTrue($this->address->isDefault());

        $this->address->setIsDefault(false);
        $this->assertFalse($this->address->isDefault());

        $this->address->setIsDefault(true);
        $this->assertTrue($this->address->isDefault());
    }

    public function testToString(): void
    {
        $this->address->setFirstName('John');
        $this->address->setLastName('Doe');
        $this->address->setAddressLine1('123 Main St');
        $this->address->setCity('New York');

        $expected = 'John Doe - 123 Main St, New York';
        $this->assertEquals($expected, (string) $this->address);
    }

    public function testToStringWithCompany(): void
    {
        $this->address->setFirstName('John');
        $this->address->setLastName('Doe');
        $this->address->setCompany('Test Company');
        $this->address->setAddressLine1('123 Main St');
        $this->address->setCity('New York');

        $expected = 'John Doe (Test Company) - 123 Main St, New York';
        $this->assertEquals($expected, (string) $this->address);
    }
} 