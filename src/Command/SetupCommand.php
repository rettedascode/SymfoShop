<?php

namespace App\Command;

use App\Service\ConfigurationService;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:setup',
    description: 'Complete setup of SymfoShop application (configuration + sample data)',
)]
class SetupCommand extends Command
{
    public function __construct(
        private ConfigurationService $configurationService,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'skip-config',
                null,
                InputOption::VALUE_NONE,
                'Skip configuration initialization'
            )
            ->addOption(
                'skip-sample-data',
                null,
                InputOption::VALUE_NONE,
                'Skip sample data creation'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force setup without confirmation'
            )
            ->setHelp('This command sets up the complete SymfoShop application with configuration and sample data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $skipConfig = $input->getOption('skip-config');
        $skipSampleData = $input->getOption('skip-sample-data');
        $force = $input->getOption('force');

        $io->title('SymfoShop Application Setup');
        $io->text('This will set up your SymfoShop application with:');

        $setupSteps = [];
        if (!$skipConfig) {
            $setupSteps[] = '✅ Initialize default configuration values';
        }
        if (!$skipSampleData) {
            $setupSteps[] = '✅ Create sample categories and products';
            $setupSteps[] = '✅ Create admin user account';
        }

        if (empty($setupSteps)) {
            $io->error('No setup steps selected. Use --skip-config and --skip-sample-data to specify what to skip.');
            return Command::FAILURE;
        }

        $io->listing($setupSteps);

        if (!$force) {
            if (!$io->confirm('Do you want to continue with the setup?', false)) {
                $io->info('Setup cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            // Step 1: Initialize Configuration
            if (!$skipConfig) {
                $io->section('📋 Initializing Configuration');
                $io->text('Setting up default configuration values...');
                
                $this->configurationService->initializeDefaults();
                
                $io->success('Configuration initialized successfully!');
                $io->text('Default configuration values have been set up.');
            }

            // Step 2: Create Sample Data
            if (!$skipSampleData) {
                $io->section('📊 Creating Sample Data');
                
                // Create categories
                $io->text('Creating categories...');
                $categories = $this->createCategories();
                $io->text(sprintf('✅ Created %d categories', count($categories)));

                // Create products
                $io->text('Creating products...');
                $this->createProducts($categories);
                $io->text('✅ Created sample products');

                // Create admin user
                $io->text('Creating admin user...');
                $this->createAdminUser();
                $io->text('✅ Created admin user account');

                $this->entityManager->flush();
                $io->success('Sample data created successfully!');
            }

            // Final summary
            $io->section('🎉 Setup Complete!');
            $io->text('Your SymfoShop application is now ready to use.');
            
            if (!$skipSampleData) {
                $io->text('');
                $io->text('📋 Admin Account Details:');
                $io->text('   Email: admin@symfoshop.com');
                $io->text('   Password: admin123');
                $io->text('');
                $io->warning('⚠️  Please change the admin password after first login!');
            }

            $io->text('');
            $io->text('🌐 Access your application:');
            $io->text('   - Frontend: http://localhost');
            $io->text('   - Admin Panel: http://localhost/admin');
            $io->text('');
            $io->text('⚙️  Manage configuration:');
            $io->text('   - Admin Panel → Configuration');
            $io->text('   - Or use: php bin/console app:initialize-configuration');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Setup failed: ' . $e->getMessage());
            $io->text('Please check the error details and try again.');
            return Command::FAILURE;
        }
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