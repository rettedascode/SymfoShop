<?php

namespace App\Tests\Entity;

use App\Entity\Review;
use App\Entity\User;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ReviewTest extends TestCase
{
    private Review $review;
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        $this->review = new Review();
        $this->user = new User();
        $this->product = new Product();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->review->getId());
        $this->assertNull($this->review->getUser());
        $this->assertNull($this->review->getProduct());
        $this->assertEquals(0, $this->review->getRating());
        $this->assertNull($this->review->getTitle());
        $this->assertNull($this->review->getComment());
        $this->assertFalse($this->review->isApproved());
        $this->assertNotNull($this->review->getCreatedAt());
        $this->assertNotNull($this->review->getUpdatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $this->review->setUser($this->user);
        $this->review->setProduct($this->product);
        $this->review->setRating(5);
        $this->review->setTitle('Great Product!');
        $this->review->setComment('This is an excellent product. Highly recommended!');
        $this->review->setIsApproved(true);

        $this->assertEquals($this->user, $this->review->getUser());
        $this->assertEquals($this->product, $this->review->getProduct());
        $this->assertEquals(5, $this->review->getRating());
        $this->assertEquals('Great Product!', $this->review->getTitle());
        $this->assertEquals('This is an excellent product. Highly recommended!', $this->review->getComment());
        $this->assertTrue($this->review->isApproved());
    }

    public function testSetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->review->setCreatedAt($date);
        $this->assertEquals($date, $this->review->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->review->setUpdatedAt($date);
        $this->assertEquals($date, $this->review->getUpdatedAt());
    }

    public function testValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        // Test valid review
        $this->review->setRating(5);
        $this->review->setTitle('Great Product!');
        $this->review->setComment('This is an excellent product.');

        $violations = $validator->validate($this->review);
        $this->assertCount(0, $violations);

        // Test rating out of range (too high)
        $this->review->setRating(6);
        $violations = $validator->validate($this->review);
        $this->assertGreaterThan(0, count($violations));

        // Test rating out of range (too low)
        $this->review->setRating(0);
        $violations = $validator->validate($this->review);
        $this->assertGreaterThan(0, count($violations));

        // Test blank title
        $this->review->setRating(5);
        $this->review->setTitle('');
        $violations = $validator->validate($this->review);
        $this->assertGreaterThan(0, count($violations));

        // Test blank comment
        $this->review->setTitle('Great Product!');
        $this->review->setComment('');
        $violations = $validator->validate($this->review);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testRatingRange(): void
    {
        // Test valid ratings
        $this->review->setRating(1);
        $this->assertEquals(1, $this->review->getRating());

        $this->review->setRating(3);
        $this->assertEquals(3, $this->review->getRating());

        $this->review->setRating(5);
        $this->assertEquals(5, $this->review->getRating());
    }

    public function testApprovalStatus(): void
    {
        $this->assertFalse($this->review->isApproved());

        $this->review->setIsApproved(true);
        $this->assertTrue($this->review->isApproved());

        $this->review->setIsApproved(false);
        $this->assertFalse($this->review->isApproved());
    }

    public function testNullableFields(): void
    {
        $this->review->setUser(null);
        $this->review->setProduct(null);
        $this->review->setTitle(null);
        $this->review->setComment(null);

        $this->assertNull($this->review->getUser());
        $this->assertNull($this->review->getProduct());
        $this->assertNull($this->review->getTitle());
        $this->assertNull($this->review->getComment());
    }

    public function testUserRelationship(): void
    {
        $this->review->setUser($this->user);
        $this->assertEquals($this->user, $this->review->getUser());
    }

    public function testProductRelationship(): void
    {
        $this->review->setProduct($this->product);
        $this->assertEquals($this->product, $this->review->getProduct());
    }

    public function testReviewContent(): void
    {
        $title = 'Amazing Product!';
        $comment = 'This product exceeded my expectations. The quality is outstanding and the price is reasonable. I would definitely recommend it to others.';

        $this->review->setTitle($title);
        $this->review->setComment($comment);

        $this->assertEquals($title, $this->review->getTitle());
        $this->assertEquals($comment, $this->review->getComment());
    }

    public function testRatingValidation(): void
    {
        // Test edge cases
        $this->review->setRating(1);
        $this->assertEquals(1, $this->review->getRating());

        $this->review->setRating(5);
        $this->assertEquals(5, $this->review->getRating());
    }

    public function testTimestamps(): void
    {
        $createdAt = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2023-01-01 11:00:00');

        $this->review->setCreatedAt($createdAt);
        $this->review->setUpdatedAt($updatedAt);

        $this->assertEquals($createdAt, $this->review->getCreatedAt());
        $this->assertEquals($updatedAt, $this->review->getUpdatedAt());
    }
} 