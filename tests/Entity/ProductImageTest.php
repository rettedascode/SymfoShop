<?php

namespace App\Tests\Entity;

use App\Entity\ProductImage;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ProductImageTest extends TestCase
{
    private ProductImage $productImage;
    private Product $product;

    protected function setUp(): void
    {
        $this->productImage = new ProductImage();
        $this->product = new Product();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->productImage->getId());
        $this->assertNull($this->productImage->getProduct());
        $this->assertNull($this->productImage->getFilename());
        $this->assertNull($this->productImage->getAltText());
        $this->assertEquals(0, $this->productImage->getSortOrder());
        $this->assertFalse($this->productImage->isMain());
        $this->assertNotNull($this->productImage->getCreatedAt());
        $this->assertNotNull($this->productImage->getUpdatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $this->productImage->setProduct($this->product);
        $this->productImage->setFilename('test-image.jpg');
        $this->productImage->setAltText('Test Product Image');
        $this->productImage->setSortOrder(5);
        $this->productImage->setIsMain(true);

        $this->assertEquals($this->product, $this->productImage->getProduct());
        $this->assertEquals('test-image.jpg', $this->productImage->getFilename());
        $this->assertEquals('Test Product Image', $this->productImage->getAltText());
        $this->assertEquals(5, $this->productImage->getSortOrder());
        $this->assertTrue($this->productImage->isMain());
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->productImage->setCreatedAt($date);
        $this->assertEquals($date, $this->productImage->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->productImage->setUpdatedAt($date);
        $this->assertEquals($date, $this->productImage->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid product image
        $this->productImage->setFilename('test-image.jpg');
        $this->productImage->setAltText('Test Product Image');
        $this->productImage->setSortOrder(1);

        $violations = $validator->validate($this->productImage);
        $this->assertCount(0, $violations);

        // Test blank filename
        $this->productImage->setFilename('');
        $violations = $validator->validate($this->productImage);
        $this->assertGreaterThan(0, count($violations));

        // Test blank alt text
        $this->productImage->setFilename('test-image.jpg');
        $this->productImage->setAltText('');
        $violations = $validator->validate($this->productImage);
        $this->assertGreaterThan(0, count($violations));

        // Test negative sort order
        $this->productImage->setAltText('Test Product Image');
        $this->productImage->setSortOrder(-1);
        $violations = $validator->validate($this->productImage);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testSortOrderManagement(): void
    {
        $this->productImage->setSortOrder(10);
        $this->assertEquals(10, $this->productImage->getSortOrder());

        $this->productImage->setSortOrder(0);
        $this->assertEquals(0, $this->productImage->getSortOrder());

        $this->productImage->setSortOrder(999);
        $this->assertEquals(999, $this->productImage->getSortOrder());
    }

    public function testMainImageStatus(): void
    {
        $this->assertFalse($this->productImage->isMain());

        $this->productImage->setIsMain(true);
        $this->assertTrue($this->productImage->isMain());

        $this->productImage->setIsMain(false);
        $this->assertFalse($this->productImage->isMain());
    }

    public function testNullableFields(): void
    {
        $this->productImage->setProduct(null);
        $this->productImage->setAltText(null);

        $this->assertNull($this->productImage->getProduct());
        $this->assertNull($this->productImage->getAltText());
    }

    public function testProductRelationship(): void
    {
        $this->productImage->setProduct($this->product);
        $this->assertEquals($this->product, $this->productImage->getProduct());
    }

    public function testFilenameManagement(): void
    {
        $filename = 'product-image-123.jpg';
        $this->productImage->setFilename($filename);
        $this->assertEquals($filename, $this->productImage->getFilename());

        $filename2 = 'updated-image.png';
        $this->productImage->setFilename($filename2);
        $this->assertEquals($filename2, $this->productImage->getFilename());
    }

    public function testAltTextManagement(): void
    {
        $altText = 'Product image showing the front view';
        $this->productImage->setAltText($altText);
        $this->assertEquals($altText, $this->productImage->getAltText());

        $altText2 = 'Product image showing the back view';
        $this->productImage->setAltText($altText2);
        $this->assertEquals($altText2, $this->productImage->getAltText());
    }

    public function testImageFileExtensions(): void
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($extensions as $extension) {
            $filename = "test-image.{$extension}";
            $this->productImage->setFilename($filename);
            $this->assertEquals($filename, $this->productImage->getFilename());
        }
    }

    public function testSortOrderEdgeCases(): void
    {
        // Test minimum value
        $this->productImage->setSortOrder(0);
        $this->assertEquals(0, $this->productImage->getSortOrder());

        // Test maximum reasonable value
        $this->productImage->setSortOrder(9999);
        $this->assertEquals(9999, $this->productImage->getSortOrder());
    }

    public function testTimestamps(): void
    {
        $createdAt = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2023-01-01 11:00:00');

        $this->productImage->setCreatedAt($createdAt);
        $this->productImage->setUpdatedAt($updatedAt);

        $this->assertEquals($createdAt, $this->productImage->getCreatedAt());
        $this->assertEquals($updatedAt, $this->productImage->getUpdatedAt());
    }
} 