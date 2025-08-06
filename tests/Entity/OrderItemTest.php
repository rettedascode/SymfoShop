<?php

namespace App\Tests\Entity;

use App\Entity\OrderItem;
use App\Entity\Order;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class OrderItemTest extends TestCase
{
    private OrderItem $orderItem;
    private Order $order;
    private Product $product;

    protected function setUp(): void
    {
        $this->orderItem = new OrderItem();
        $this->order = new Order();
        $this->product = new Product();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->orderItem->getId());
        $this->assertNull($this->orderItem->getOrder());
        $this->assertNull($this->orderItem->getProduct());
        $this->assertEquals(0, $this->orderItem->getQuantity());
        $this->assertEquals(0.0, $this->orderItem->getUnitPrice());
        $this->assertEquals(0.0, $this->orderItem->getTotalPrice());
        $this->assertNull($this->orderItem->getProductName());
        $this->assertNull($this->orderItem->getProductSku());
    }

    public function testSettersAndGetters(): void
    {
        $this->orderItem->setOrder($this->order);
        $this->orderItem->setProduct($this->product);
        $this->orderItem->setQuantity(5);
        $this->orderItem->setUnitPrice(29.99);
        $this->orderItem->setTotalPrice(149.95);
        $this->orderItem->setProductName('Test Product');
        $this->orderItem->setProductSku('TEST-001');

        $this->assertEquals($this->order, $this->orderItem->getOrder());
        $this->assertEquals($this->product, $this->orderItem->getProduct());
        $this->assertEquals(5, $this->orderItem->getQuantity());
        $this->assertEquals(29.99, $this->orderItem->getUnitPrice());
        $this->assertEquals(149.95, $this->orderItem->getTotalPrice());
        $this->assertEquals('Test Product', $this->orderItem->getProductName());
        $this->assertEquals('TEST-001', $this->orderItem->getProductSku());
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->orderItem->setCreatedAt($date);
        $this->assertEquals($date, $this->orderItem->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->orderItem->setUpdatedAt($date);
        $this->assertEquals($date, $this->orderItem->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid order item
        $this->orderItem->setQuantity(5);
        $this->orderItem->setUnitPrice(29.99);
        $this->orderItem->setTotalPrice(149.95);

        $violations = $validator->validate($this->orderItem);
        $this->assertCount(0, $violations);

        // Test negative quantity
        $this->orderItem->setQuantity(-1);
        $violations = $validator->validate($this->orderItem);
        $this->assertGreaterThan(0, count($violations));

        // Test negative unit price
        $this->orderItem->setQuantity(5);
        $this->orderItem->setUnitPrice(-10.0);
        $violations = $validator->validate($this->orderItem);
        $this->assertGreaterThan(0, count($violations));

        // Test negative total price
        $this->orderItem->setUnitPrice(29.99);
        $this->orderItem->setTotalPrice(-50.0);
        $violations = $validator->validate($this->orderItem);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testNullableFields(): void
    {
        $this->orderItem->setOrder(null);
        $this->orderItem->setProduct(null);
        $this->orderItem->setProductName(null);
        $this->orderItem->setProductSku(null);

        $this->assertNull($this->orderItem->getOrder());
        $this->assertNull($this->orderItem->getProduct());
        $this->assertNull($this->orderItem->getProductName());
        $this->assertNull($this->orderItem->getProductSku());
    }

    public function testQuantityManagement(): void
    {
        $this->orderItem->setQuantity(10);
        $this->assertEquals(10, $this->orderItem->getQuantity());

        $this->orderItem->setQuantity(0);
        $this->assertEquals(0, $this->orderItem->getQuantity());
    }

    public function testPriceCalculations(): void
    {
        $this->orderItem->setUnitPrice(25.0);
        $this->orderItem->setQuantity(4);
        $this->orderItem->setTotalPrice(100.0);

        $this->assertEquals(25.0, $this->orderItem->getUnitPrice());
        $this->assertEquals(4, $this->orderItem->getQuantity());
        $this->assertEquals(100.0, $this->orderItem->getTotalPrice());
    }

    public function testProductInformation(): void
    {
        $this->orderItem->setProductName('Test Product');
        $this->orderItem->setProductSku('TEST-001');

        $this->assertEquals('Test Product', $this->orderItem->getProductName());
        $this->assertEquals('TEST-001', $this->orderItem->getProductSku());
    }

    public function testOrderRelationship(): void
    {
        $this->orderItem->setOrder($this->order);
        $this->assertEquals($this->order, $this->orderItem->getOrder());
    }

    public function testProductRelationship(): void
    {
        $this->orderItem->setProduct($this->product);
        $this->assertEquals($this->product, $this->orderItem->getProduct());
    }
} 