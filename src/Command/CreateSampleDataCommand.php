<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-sample-data',
    description: 'Creates sample data for the shop',
)]
class CreateSampleDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating Sample Data');

        // Create categories
        $categories = $this->createCategories();
        $io->text('Categories created');

        // Create products
        $this->createProducts($categories);
        $io->text('Products created');

        // Create admin user
        $this->createAdminUser();
        $io->text('Admin user created');

        $this->entityManager->flush();

        $io->success('Sample data created successfully!');

        return Command::SUCCESS;
    }

    private function createCategories(): array
    {
        $categories = [];

        $categoryData = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Books', 'description' => 'Books and literature'],
            ['name' => 'Home & Garden', 'description' => 'Home improvement and garden supplies'],
        ];

        foreach ($categoryData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setDescription($data['description']);
            $category->setSlug(strtolower(str_replace(' ', '-', $data['name'])));
            $category->setIsActive(true);

            $this->entityManager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }

    private function createProducts(array $categories): void
    {
        $productData = [
            [
                'name' => 'Smartphone X',
                'description' => 'Latest smartphone with advanced features',
                'price' => 599.99,
                'comparePrice' => 699.99,
                'stock' => 50,
                'sku' => 'SMART-X-001',
                'category' => $categories[0], // Electronics
                'featured' => true,
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'Professional laptop for work and gaming',
                'price' => 1299.99,
                'comparePrice' => 1499.99,
                'stock' => 25,
                'sku' => 'LAPTOP-PRO-001',
                'category' => $categories[0], // Electronics
                'featured' => true,
            ],
            [
                'name' => 'Cotton T-Shirt',
                'description' => 'Comfortable cotton t-shirt in various colors',
                'price' => 19.99,
                'comparePrice' => 24.99,
                'stock' => 100,
                'sku' => 'TSHIRT-001',
                'category' => $categories[1], // Clothing
                'featured' => false,
            ],
            [
                'name' => 'Programming Book',
                'description' => 'Learn programming from scratch',
                'price' => 39.99,
                'comparePrice' => 49.99,
                'stock' => 75,
                'sku' => 'BOOK-PROG-001',
                'category' => $categories[2], // Books
                'featured' => true,
            ],
            [
                'name' => 'Garden Tool Set',
                'description' => 'Complete set of essential garden tools',
                'price' => 89.99,
                'comparePrice' => 119.99,
                'stock' => 30,
                'sku' => 'GARDEN-TOOLS-001',
                'category' => $categories[3], // Home & Garden
                'featured' => false,
            ],
        ];

        foreach ($productData as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setComparePrice($data['comparePrice']);
            $product->setStock($data['stock']);
            $product->setSku($data['sku']);
            $product->setCategory($data['category']);
            $product->setIsActive(true);
            $product->setIsFeatured($data['featured']);
            $product->setSlug(strtolower(str_replace(' ', '-', $data['name'])));

            $this->entityManager->persist($product);
        }
    }

    private function createAdminUser(): void
    {
        $user = new User();
        $user->setEmail('admin@symfoshop.com');
        $user->setFirstName('Admin');
        $user->setLastName('User');
        $user->setPhone('123-456-7890');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));
        $user->setIsActive(true);

        $this->entityManager->persist($user);
    }
} 