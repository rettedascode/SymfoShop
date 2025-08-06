<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class CategoryTest extends TestCase
{
    private Category $category;
    private Category $parentCategory;

    protected function setUp(): void
    {
        $this->category = new Category();
        $this->parentCategory = new Category();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->category->getId());
        $this->assertNull($this->category->getName());
        $this->assertNull($this->category->getDescription());
        $this->assertNull($this->category->getSlug());
        $this->assertTrue($this->category->isActive());
        $this->assertNotNull($this->category->getCreatedAt());
        $this->assertNotNull($this->category->getUpdatedAt());
        $this->assertNull($this->category->getParent());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->category->getChildren());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->category->getProducts());
    }

    public function testSettersAndGetters(): void
    {
        $this->category->setName('Test Category');
        $this->category->setDescription('Test Description');
        $this->category->setSlug('test-category');
        $this->category->setIsActive(false);
        $this->category->setParent($this->parentCategory);

        $this->assertEquals('Test Category', $this->category->getName());
        $this->assertEquals('Test Description', $this->category->getDescription());
        $this->assertEquals('test-category', $this->category->getSlug());
        $this->assertFalse($this->category->isActive());
        $this->assertEquals($this->parentCategory, $this->category->getParent());
    }

    public function testAddAndRemoveChild(): void
    {
        $childCategory = new Category();
        
        $this->assertCount(0, $this->category->getChildren());
        
        $this->category->addChild($childCategory);
        $this->assertCount(1, $this->category->getChildren());
        $this->assertTrue($this->category->getChildren()->contains($childCategory));
        $this->assertEquals($this->category, $childCategory->getParent());
        
        $this->category->removeChild($childCategory);
        $this->assertCount(0, $this->category->getChildren());
        $this->assertFalse($this->category->getChildren()->contains($childCategory));
        $this->assertNull($childCategory->getParent());
    }

    public function testAddAndRemoveProduct(): void
    {
        $product = new Product();
        
        $this->assertCount(0, $this->category->getProducts());
        
        $this->category->addProduct($product);
        $this->assertCount(1, $this->category->getProducts());
        $this->assertTrue($this->category->getProducts()->contains($product));
        $this->assertEquals($this->category, $product->getCategory());
        
        $this->category->removeProduct($product);
        $this->assertCount(0, $this->category->getProducts());
        $this->assertFalse($this->category->getProducts()->contains($product));
        $this->assertNull($product->getCategory());
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->category->setCreatedAt($date);
        $this->assertEquals($date, $this->category->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->category->setUpdatedAt($date);
        $this->assertEquals($date, $this->category->getUpdatedAt());
    }

    public function testToString(): void
    {
        $this->category->setName('Test Category');
        $this->assertEquals('Test Category', (string) $this->category);
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid category
        $this->category->setName('Test Category');

        $violations = $validator->validate($this->category);
        $this->assertCount(0, $violations);

        // Test blank name
        $this->category->setName('');
        $violations = $validator->validate($this->category);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testNullableFields(): void
    {
        $this->category->setDescription(null);
        $this->category->setSlug(null);
        $this->category->setParent(null);

        $this->assertNull($this->category->getDescription());
        $this->assertNull($this->category->getSlug());
        $this->assertNull($this->category->getParent());
    }

    public function testActiveStatus(): void
    {
        $this->assertTrue($this->category->isActive());

        $this->category->setIsActive(false);
        $this->assertFalse($this->category->isActive());

        $this->category->setIsActive(true);
        $this->assertTrue($this->category->isActive());
    }

    public function testParentChildRelationship(): void
    {
        $parent = new Category();
        $parent->setName('Parent Category');
        
        $child = new Category();
        $child->setName('Child Category');
        
        $parent->addChild($child);
        
        $this->assertEquals($parent, $child->getParent());
        $this->assertTrue($parent->getChildren()->contains($child));
        $this->assertCount(1, $parent->getChildren());
    }

    public function testRemoveChildUpdatesParent(): void
    {
        $parent = new Category();
        $child = new Category();
        
        $parent->addChild($child);
        $this->assertEquals($parent, $child->getParent());
        
        $parent->removeChild($child);
        $this->assertNull($child->getParent());
        $this->assertCount(0, $parent->getChildren());
    }

    public function testProductRelationship(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        
        $product = new Product();
        $product->setName('Test Product');
        
        $category->addProduct($product);
        
        $this->assertEquals($category, $product->getCategory());
        $this->assertTrue($category->getProducts()->contains($product));
        $this->assertCount(1, $category->getProducts());
    }

    public function testRemoveProductUpdatesCategory(): void
    {
        $category = new Category();
        $product = new Product();
        
        $category->addProduct($product);
        $this->assertEquals($category, $product->getCategory());
        
        $category->removeProduct($product);
        $this->assertNull($product->getCategory());
        $this->assertCount(0, $category->getProducts());
    }

    public function testHierarchicalStructure(): void
    {
        $root = new Category();
        $root->setName('Root Category');
        
        $child1 = new Category();
        $child1->setName('Child 1');
        
        $child2 = new Category();
        $child2->setName('Child 2');
        
        $grandchild = new Category();
        $grandchild->setName('Grandchild');
        
        $root->addChild($child1);
        $root->addChild($child2);
        $child1->addChild($grandchild);
        
        $this->assertCount(2, $root->getChildren());
        $this->assertCount(1, $child1->getChildren());
        $this->assertCount(0, $child2->getChildren());
        $this->assertCount(0, $grandchild->getChildren());
        
        $this->assertEquals($root, $child1->getParent());
        $this->assertEquals($root, $child2->getParent());
        $this->assertEquals($child1, $grandchild->getParent());
    }
} 