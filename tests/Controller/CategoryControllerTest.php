<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CategoryControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testCategoriesIndexPage(): void
    {
        $this->client->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Browse Categories');
        $this->assertSelectorExists('nav[aria-label="breadcrumb"]');
    }

    public function testCategoriesIndexWithCategories(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $category->setIsActive(true);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->client->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.card-title', 'Test Category');
        $this->assertSelectorTextContains('.card-text', 'Test Description');
    }

    public function testCategoryShowPage(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $category->setIsActive(true);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->client->request('GET', '/categories/' . $category->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Test Category');
    }

    public function testCategoryShowPageNotFound(): void
    {
        $this->client->request('GET', '/categories/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCategoryShowPageInactiveCategory(): void
    {
        // Create an inactive category
        $category = new Category();
        $category->setName('Inactive Category');
        $category->setIsActive(false);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->client->request('GET', '/categories/' . $category->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCategoriesIndexEmptyState(): void
    {
        // Clear all categories
        $this->entityManager->createQuery('DELETE FROM App\Entity\Category')->execute();

        $this->client->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'No categories available');
        $this->assertSelectorExists('a[href="/products"]');
    }

    public function testCategoriesIndexBreadcrumbNavigation(): void
    {
        $this->client->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('nav[aria-label="breadcrumb"]');
        $this->assertSelectorExists('a[href="/"]');
        $this->assertSelectorTextContains('.breadcrumb-item.active', 'Categories');
    }

    public function testCategoriesIndexResponsiveDesign(): void
    {
        $this->client->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.col-md-4.col-lg-3');
        $this->assertSelectorExists('.card.category-card');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test data
        $this->entityManager->createQuery('DELETE FROM App\Entity\Category c WHERE c.name LIKE :name')
            ->setParameter('name', 'Test%')
            ->execute();
    }
} 