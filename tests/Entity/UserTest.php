<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Review;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->user->getId());
        $this->assertNull($this->user->getEmail());
        $this->assertEquals([], $this->user->getRoles());
        $this->assertNull($this->user->getPassword());
        $this->assertNull($this->user->getFirstName());
        $this->assertNull($this->user->getLastName());
        $this->assertNull($this->user->getPhone());
        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertNotNull($this->user->getUpdatedAt());
        $this->assertTrue($this->user->isActive());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->user->getOrders());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->user->getAddresses());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->user->getReviews());
    }

    public function testSettersAndGetters(): void
    {
        $this->user->setEmail('test@example.com');
        $this->user->setRoles(['ROLE_USER']);
        $this->user->setPassword('hashed_password');
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->user->setPhone('+1-555-0123');
        $this->user->setIsActive(false);

        $this->assertEquals('test@example.com', $this->user->getEmail());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
        $this->assertEquals('hashed_password', $this->user->getPassword());
        $this->assertEquals('John', $this->user->getFirstName());
        $this->assertEquals('Doe', $this->user->getLastName());
        $this->assertEquals('+1-555-0123', $this->user->getPhone());
        $this->assertFalse($this->user->isActive());
    }

    public function testGetUserIdentifier(): void
    {
        $this->user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $this->user->getUserIdentifier());
    }

    public function testGetFullName(): void
    {
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->assertEquals('John Doe', $this->user->getFullName());
    }

    public function testGetFullNameWithNullValues(): void
    {
        $this->assertEquals(' ', $this->user->getFullName());
    }

    public function testEraseCredentials(): void
    {
        // This method should not throw any exception
        $this->user->eraseCredentials();
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }

    public function testAddAndRemoveOrder(): void
    {
        $order = new Order();
        
        $this->assertCount(0, $this->user->getOrders());
        
        $this->user->addOrder($order);
        $this->assertCount(1, $this->user->getOrders());
        $this->assertTrue($this->user->getOrders()->contains($order));
        
        $this->user->removeOrder($order);
        $this->assertCount(0, $this->user->getOrders());
        $this->assertFalse($this->user->getOrders()->contains($order));
    }

    public function testAddAndRemoveAddress(): void
    {
        $address = new Address();
        
        $this->assertCount(0, $this->user->getAddresses());
        
        $this->user->addAddress($address);
        $this->assertCount(1, $this->user->getAddresses());
        $this->assertTrue($this->user->getAddresses()->contains($address));
        
        $this->user->removeAddress($address);
        $this->assertCount(0, $this->user->getAddresses());
        $this->assertFalse($this->user->getAddresses()->contains($address));
    }

    public function testAddAndRemoveReview(): void
    {
        $review = new Review();
        
        $this->assertCount(0, $this->user->getReviews());
        
        $this->user->addReview($review);
        $this->assertCount(1, $this->user->getReviews());
        $this->assertTrue($this->user->getReviews()->contains($review));
        
        $this->user->removeReview($review);
        $this->assertCount(0, $this->user->getReviews());
        $this->assertFalse($this->user->getReviews()->contains($review));
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->user->setCreatedAt($date);
        $this->assertEquals($date, $this->user->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->user->setUpdatedAt($date);
        $this->assertEquals($date, $this->user->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid user
        $this->user->setEmail('test@example.com');
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');

        $violations = $validator->validate($this->user);
        $this->assertCount(0, $violations);

        // Test invalid email
        $this->user->setEmail('invalid-email');
        $violations = $validator->validate($this->user);
        $this->assertGreaterThan(0, count($violations));

        // Test blank email
        $this->user->setEmail('');
        $violations = $validator->validate($this->user);
        $this->assertGreaterThan(0, count($violations));

        // Test blank first name
        $this->user->setEmail('test@example.com');
        $this->user->setFirstName('');
        $violations = $validator->validate($this->user);
        $this->assertGreaterThan(0, count($violations));

        // Test blank last name
        $this->user->setFirstName('John');
        $this->user->setLastName('');
        $violations = $validator->validate($this->user);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testRolesManagement(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $this->user->getRoles());

        // Test that roles are unique
        $this->user->setRoles(['ROLE_USER', 'ROLE_USER', 'ROLE_ADMIN']);
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $this->user->getRoles());
    }

    public function testPhoneCanBeNull(): void
    {
        $this->user->setPhone(null);
        $this->assertNull($this->user->getPhone());

        $this->user->setPhone('+1-555-0123');
        $this->assertEquals('+1-555-0123', $this->user->getPhone());
    }

    public function testActiveStatus(): void
    {
        $this->assertTrue($this->user->isActive());

        $this->user->setIsActive(false);
        $this->assertFalse($this->user->isActive());

        $this->user->setIsActive(true);
        $this->assertTrue($this->user->isActive());
    }
} 