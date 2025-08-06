<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\OrderItem;
use App\Entity\Address;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class OrderTest extends TestCase
{
    private Order $order;
    private User $user;

    protected function setUp(): void
    {
        $this->order = new Order();
        $this->user = new User();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->order->getId());
        $this->assertNull($this->order->getOrderNumber());
        $this->assertEquals('pending', $this->order->getStatus());
        $this->assertEquals(0.0, $this->order->getSubtotal());
        $this->assertEquals(0.0, $this->order->getTax());
        $this->assertEquals(0.0, $this->order->getShipping());
        $this->assertEquals(0.0, $this->order->getTotal());
        $this->assertNull($this->order->getUser());
        $this->assertNull($this->order->getShippingAddress());
        $this->assertNull($this->order->getBillingAddress());
        $this->assertNotNull($this->order->getCreatedAt());
        $this->assertNotNull($this->order->getUpdatedAt());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->order->getOrderItems());
    }

    public function testSettersAndGetters(): void
    {
        $this->order->setOrderNumber('ORD-001');
        $this->order->setStatus('completed');
        $this->order->setSubtotal(100.0);
        $this->order->setTax(10.0);
        $this->order->setShipping(5.0);
        $this->order->setTotal(115.0);
        $this->order->setUser($this->user);
        $this->order->setNotes('Test notes');

        $this->assertEquals('ORD-001', $this->order->getOrderNumber());
        $this->assertEquals('completed', $this->order->getStatus());
        $this->assertEquals(100.0, $this->order->getSubtotal());
        $this->assertEquals(10.0, $this->order->getTax());
        $this->assertEquals(5.0, $this->order->getShipping());
        $this->assertEquals(115.0, $this->order->getTotal());
        $this->assertEquals($this->user, $this->order->getUser());
        $this->assertEquals('Test notes', $this->order->getNotes());
    }

    public function testAddAndRemoveOrderItem(): void
    {
        $orderItem = new OrderItem();
        
        $this->assertCount(0, $this->order->getOrderItems());
        
        $this->order->addOrderItem($orderItem);
        $this->assertCount(1, $this->order->getOrderItems());
        $this->assertTrue($this->order->getOrderItems()->contains($orderItem));
        $this->assertEquals($this->order, $orderItem->getOrder());
        
        $this->order->removeOrderItem($orderItem);
        $this->assertCount(0, $this->order->getOrderItems());
        $this->assertFalse($this->order->getOrderItems()->contains($orderItem));
        $this->assertNull($orderItem->getOrder());
    }

    public function testSetAddresses(): void
    {
        $shippingAddress = new Address();
        $billingAddress = new Address();
        
        $this->order->setShippingAddress($shippingAddress);
        $this->order->setBillingAddress($billingAddress);
        
        $this->assertEquals($shippingAddress, $this->order->getShippingAddress());
        $this->assertEquals($billingAddress, $this->order->getBillingAddress());
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->order->setCreatedAt($date);
        $this->assertEquals($date, $this->order->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->order->setUpdatedAt($date);
        $this->assertEquals($date, $this->order->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid order
        $this->order->setOrderNumber('ORD-001');
        $this->order->setSubtotal(100.0);
        $this->order->setTax(10.0);
        $this->order->setShipping(5.0);
        $this->order->setTotal(115.0);

        $violations = $validator->validate($this->order);
        $this->assertCount(0, $violations);

        // Test negative values
        $this->order->setSubtotal(-10.0);
        $violations = $validator->validate($this->order);
        $this->assertGreaterThan(0, count($violations));

        $this->order->setSubtotal(100.0);
        $this->order->setTax(-5.0);
        $violations = $validator->validate($this->order);
        $this->assertGreaterThan(0, count($violations));

        $this->order->setTax(10.0);
        $this->order->setShipping(-2.0);
        $violations = $validator->validate($this->order);
        $this->assertGreaterThan(0, count($violations));

        $this->order->setShipping(5.0);
        $this->order->setTotal(-50.0);
        $violations = $validator->validate($this->order);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testStatusManagement(): void
    {
        $this->assertEquals('pending', $this->order->getStatus());

        $this->order->setStatus('processing');
        $this->assertEquals('processing', $this->order->getStatus());

        $this->order->setStatus('completed');
        $this->assertEquals('completed', $this->order->getStatus());

        $this->order->setStatus('cancelled');
        $this->assertEquals('cancelled', $this->order->getStatus());
    }

    public function testNullableFields(): void
    {
        $this->order->setNotes(null);
        $this->order->setShippingAddress(null);
        $this->order->setBillingAddress(null);
        $this->order->setUser(null);

        $this->assertNull($this->order->getNotes());
        $this->assertNull($this->order->getShippingAddress());
        $this->assertNull($this->order->getBillingAddress());
        $this->assertNull($this->order->getUser());
    }

    public function testOrderNumberGeneration(): void
    {
        $this->order->setOrderNumber('ORD-2023-001');
        $this->assertEquals('ORD-2023-001', $this->order->getOrderNumber());
    }

    public function testTotalCalculation(): void
    {
        $this->order->setSubtotal(100.0);
        $this->order->setTax(10.0);
        $this->order->setShipping(5.0);
        $this->order->setTotal(115.0);

        $this->assertEquals(115.0, $this->order->getTotal());
    }

    public function testOrderItemsCollection(): void
    {
        $item1 = new OrderItem();
        $item2 = new OrderItem();
        
        $this->order->addOrderItem($item1);
        $this->order->addOrderItem($item2);
        
        $this->assertCount(2, $this->order->getOrderItems());
        $this->assertTrue($this->order->getOrderItems()->contains($item1));
        $this->assertTrue($this->order->getOrderItems()->contains($item2));
    }
} 