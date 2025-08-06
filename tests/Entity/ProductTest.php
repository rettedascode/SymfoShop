<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductImage;
use App\Entity\OrderItem;
use App\Entity\Review;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ProductTest extends TestCase
{
    private Product $product;
    private Category $category;

    protected function setUp(): void
    {
        $this->product = new Product();
        $this->category = new Category();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->product->getId());
        $this->assertNull($this->product->getName());
        $this->assertNull($this->product->getDescription());
        $this->assertNull($this->product->getSlug());
        $this->assertNull($this->product->getSku());
        $this->assertEquals(0.0, $this->product->getPrice());
        $this->assertEquals(0.0, $this->product->getComparePrice());
        $this->assertEquals(0, $this->product->getStock());
        $this->assertTrue($this->product->isActive());
        $this->assertFalse($this->product->isFeatured());
        $this->assertNotNull($this->product->getCreatedAt());
        $this->assertNotNull($this->product->getUpdatedAt());
        $this->assertNull($this->product->getCategory());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->product->getImages());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->product->getOrderItems());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->product->getReviews());
        $this->assertEquals([], $this->product->getAttributes());
        $this->assertNull($this->product->getMetaTitle());
        $this->assertNull($this->product->getMetaDescription());
    }

    public function testSettersAndGetters(): void
    {
        $this->product->setName('Test Product');
        $this->product->setDescription('Test Description');
        $this->product->setSlug('test-product');
        $this->product->setSku('TEST-001');
        $this->product->setPrice(29.99);
        $this->product->setComparePrice(39.99);
        $this->product->setStock(100);
        $this->product->setIsActive(false);
        $this->product->setIsFeatured(true);
        $this->product->setCategory($this->category);
        $this->product->setAttributes(['color' => 'red', 'size' => 'M']);
        $this->product->setMetaTitle('Test Meta Title');
        $this->product->setMetaDescription('Test Meta Description');

        $this->assertEquals('Test Product', $this->product->getName());
        $this->assertEquals('Test Description', $this->product->getDescription());
        $this->assertEquals('test-product', $this->product->getSlug());
        $this->assertEquals('TEST-001', $this->product->getSku());
        $this->assertEquals(29.99, $this->product->getPrice());
        $this->assertEquals(39.99, $this->product->getComparePrice());
        $this->assertEquals(100, $this->product->getStock());
        $this->assertFalse($this->product->isActive());
        $this->assertTrue($this->product->isFeatured());
        $this->assertEquals($this->category, $this->product->getCategory());
        $this->assertEquals(['color' => 'red', 'size' => 'M'], $this->product->getAttributes());
        $this->assertEquals('Test Meta Title', $this->product->getMetaTitle());
        $this->assertEquals('Test Meta Description', $this->product->getMetaDescription());
    }

    public function testAddAndRemoveImage(): void
    {
        $image = new ProductImage();
        
        $this->assertCount(0, $this->product->getImages());
        
        $this->product->addImage($image);
        $this->assertCount(1, $this->product->getImages());
        $this->assertTrue($this->product->getImages()->contains($image));
        
        $this->product->removeImage($image);
        $this->assertCount(0, $this->product->getImages());
        $this->assertFalse($this->product->getImages()->contains($image));
    }

    public function testAddAndRemoveOrderItem(): void
    {
        $orderItem = new OrderItem();
        
        $this->assertCount(0, $this->product->getOrderItems());
        
        $this->product->addOrderItem($orderItem);
        $this->assertCount(1, $this->product->getOrderItems());
        $this->assertTrue($this->product->getOrderItems()->contains($orderItem));
        
        $this->product->removeOrderItem($orderItem);
        $this->assertCount(0, $this->product->getOrderItems());
        $this->assertFalse($this->product->getOrderItems()->contains($orderItem));
    }

    public function testAddAndRemoveReview(): void
    {
        $review = new Review();
        
        $this->assertCount(0, $this->product->getReviews());
        
        $this->product->addReview($review);
        $this->assertCount(1, $this->product->getReviews());
        $this->assertTrue($this->product->getReviews()->contains($review));
        
        $this->product->removeReview($review);
        $this->assertCount(0, $this->product->getReviews());
        $this->assertFalse($this->product->getReviews()->contains($review));
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->product->setCreatedAt($date);
        $this->assertEquals($date, $this->product->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->product->setUpdatedAt($date);
        $this->assertEquals($date, $this->product->getUpdatedAt());
    }

    public function testGetAverageRating(): void
    {
        // Test with no reviews
        $this->assertEquals(0.0, $this->product->getAverageRating());

        // Test with reviews
        $review1 = new Review();
        $review1->setRating(4);
        $review1->setIsApproved(true);
        
        $review2 = new Review();
        $review2->setRating(5);
        $review2->setIsApproved(true);
        
        $review3 = new Review();
        $review3->setRating(3);
        $review3->setIsApproved(false); // Should not be counted
        
        $this->product->addReview($review1);
        $this->product->addReview($review2);
        $this->product->addReview($review3);
        
        $this->assertEquals(4.5, $this->product->getAverageRating());
    }

    public function testGetMainImage(): void
    {
        // Test with no images
        $this->assertNull($this->product->getMainImage());

        // Test with images
        $image1 = new ProductImage();
        $image1->setIsMain(false);
        
        $image2 = new ProductImage();
        $image2->setIsMain(true);
        
        $this->product->addImage($image1);
        $this->product->addImage($image2);
        
        $this->assertEquals($image2, $this->product->getMainImage());
    }

    public function testHasStock(): void
    {
        $this->product->setStock(10);
        
        $this->assertTrue($this->product->hasStock(1));
        $this->assertTrue($this->product->hasStock(10));
        $this->assertFalse($this->product->hasStock(11));
        $this->assertFalse($this->product->hasStock(0));
    }

    public function testToString(): void
    {
        $this->product->setName('Test Product');
        $this->assertEquals('Test Product', (string) $this->product);
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid product
        $this->product->setName('Test Product');
        $this->product->setPrice(29.99);
        $this->product->setComparePrice(39.99);
        $this->product->setStock(100);

        $violations = $validator->validate($this->product);
        $this->assertCount(0, $violations);

        // Test blank name
        $this->product->setName('');
        $violations = $validator->validate($this->product);
        $this->assertGreaterThan(0, count($violations));

        // Test negative price
        $this->product->setName('Test Product');
        $this->product->setPrice(-10.0);
        $violations = $validator->validate($this->product);
        $this->assertGreaterThan(0, count($violations));

        // Test negative compare price
        $this->product->setPrice(29.99);
        $this->product->setComparePrice(-10.0);
        $violations = $validator->validate($this->product);
        $this->assertGreaterThan(0, count($violations));

        // Test negative stock
        $this->product->setComparePrice(39.99);
        $this->product->setStock(-10);
        $violations = $validator->validate($this->product);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testAttributesManagement(): void
    {
        $attributes = ['color' => 'red', 'size' => 'M', 'weight' => '500g'];
        $this->product->setAttributes($attributes);
        
        $this->assertEquals($attributes, $this->product->getAttributes());
        
        // Test setting null attributes
        $this->product->setAttributes(null);
        $this->assertEquals([], $this->product->getAttributes());
    }

    public function testNullableFields(): void
    {
        $this->product->setDescription(null);
        $this->product->setSlug(null);
        $this->product->setSku(null);
        $this->product->setMetaTitle(null);
        $this->product->setMetaDescription(null);
        $this->product->setCategory(null);

        $this->assertNull($this->product->getDescription());
        $this->assertNull($this->product->getSlug());
        $this->assertNull($this->product->getSku());
        $this->assertNull($this->product->getMetaTitle());
        $this->assertNull($this->product->getMetaDescription());
        $this->assertNull($this->product->getCategory());
    }

    public function testActiveAndFeaturedStatus(): void
    {
        $this->assertTrue($this->product->isActive());
        $this->assertFalse($this->product->isFeatured());

        $this->product->setIsActive(false);
        $this->product->setIsFeatured(true);

        $this->assertFalse($this->product->isActive());
        $this->assertTrue($this->product->isFeatured());
    }
} 