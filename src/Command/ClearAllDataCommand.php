<?php

namespace App\Command;

use App\Entity\Address;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clear-all-data',
    description: 'Clears all data from the database',
)]
class ClearAllDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the operation without confirmation'
            )
            ->addOption(
                'keep-admin',
                null,
                InputOption::VALUE_NONE,
                'Keep admin users in the database'
            )
            ->addOption(
                'fast',
                null,
                InputOption::VALUE_NONE,
                'Use SQL TRUNCATE for faster execution (may not work with foreign keys)'
            )
            ->setHelp('This command clears all data from the database. Use with caution!');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $keepAdmin = $input->getOption('keep-admin');
        $fast = $input->getOption('fast');

        $io->title('Clear All Data');

        if (!$force) {
            $io->warning('This will permanently delete ALL data from the database!');
            $io->text('This includes:');
            $io->listing([
                'All users (except admin if --keep-admin is used)',
                'All products and categories',
                'All orders and order items',
                'All reviews and ratings',
                'All addresses',
                'All product images'
            ]);

            if (!$io->confirm('Are you sure you want to continue?', false)) {
                $io->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            if (!$io->confirm('This action cannot be undone. Are you absolutely sure?', false)) {
                $io->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $io->text('Starting data cleanup...');

        try {
            if ($fast) {
                $this->clearDataFast($io, $keepAdmin);
            } else {
                $this->clearDataSafe($io, $keepAdmin);
            }

            $io->success('All data cleared successfully!');

            if ($keepAdmin) {
                $io->info('Admin users have been preserved.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('An error occurred while clearing data: ' . $e->getMessage());
            $io->text('Try running without --fast option for safer execution.');
            return Command::FAILURE;
        }
    }

    private function clearDataFast(SymfonyStyle $io, bool $keepAdmin): void
    {
        $io->text('Using fast SQL TRUNCATE method...');
        
        $connection = $this->entityManager->getConnection();
        
        // Disable foreign key checks
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        
        // Get table names in the correct order
        $tables = [
            'review',
            'order_item', 
            '`order`', // order is a reserved word
            'product_image',
            'product',
            'category',
            'address'
        ];
        
        foreach ($tables as $table) {
            $io->text("Truncating table: $table");
            $connection->executeStatement("TRUNCATE TABLE $table");
        }
        
        // Handle users separately if keeping admin
        if ($keepAdmin) {
            $io->text('Removing non-admin users...');
            $connection->executeStatement("DELETE FROM user WHERE JSON_SEARCH(roles, 'one', 'ROLE_ADMIN') IS NULL");
        } else {
            $io->text('Truncating table: user');
            $connection->executeStatement('TRUNCATE TABLE user');
        }
        
        // Re-enable foreign key checks
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        
        $io->text('Fast cleanup completed!');
    }

    private function clearDataSafe(SymfonyStyle $io, bool $keepAdmin): void
    {
        $io->text('Using safe entity-based method...');
        
        // Disable foreign key checks temporarily
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        // Clear data in the correct order to avoid foreign key constraints
        $this->clearReviews($io);
        $this->clearOrderItems($io);
        $this->clearOrders($io);
        $this->clearProductImages($io);
        $this->clearProducts($io);
        $this->clearCategories($io);
        $this->clearAddresses($io);
        $this->clearUsers($io, $keepAdmin);

        // Re-enable foreign key checks
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        // Flush all changes
        $this->entityManager->flush();
    }

    private function clearReviews(SymfonyStyle $io): void
    {
        $io->text('Clearing reviews...');
        $reviews = $this->entityManager->getRepository(Review::class)->findAll();
        foreach ($reviews as $review) {
            $this->entityManager->remove($review);
        }
        $io->text(sprintf('Removed %d reviews', count($reviews)));
    }

    private function clearOrderItems(SymfonyStyle $io): void
    {
        $io->text('Clearing order items...');
        $orderItems = $this->entityManager->getRepository(OrderItem::class)->findAll();
        foreach ($orderItems as $orderItem) {
            $this->entityManager->remove($orderItem);
        }
        $io->text(sprintf('Removed %d order items', count($orderItems)));
    }

    private function clearOrders(SymfonyStyle $io): void
    {
        $io->text('Clearing orders...');
        $orders = $this->entityManager->getRepository(Order::class)->findAll();
        foreach ($orders as $order) {
            $this->entityManager->remove($order);
        }
        $io->text(sprintf('Removed %d orders', count($orders)));
    }

    private function clearProductImages(SymfonyStyle $io): void
    {
        $io->text('Clearing product images...');
        $productImages = $this->entityManager->getRepository(ProductImage::class)->findAll();
        foreach ($productImages as $productImage) {
            $this->entityManager->remove($productImage);
        }
        $io->text(sprintf('Removed %d product images', count($productImages)));
    }

    private function clearProducts(SymfonyStyle $io): void
    {
        $io->text('Clearing products...');
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        foreach ($products as $product) {
            $this->entityManager->remove($product);
        }
        $io->text(sprintf('Removed %d products', count($products)));
    }

    private function clearCategories(SymfonyStyle $io): void
    {
        $io->text('Clearing categories...');
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        foreach ($categories as $category) {
            $this->entityManager->remove($category);
        }
        $io->text(sprintf('Removed %d categories', count($categories)));
    }

    private function clearAddresses(SymfonyStyle $io): void
    {
        $io->text('Clearing addresses...');
        $addresses = $this->entityManager->getRepository(Address::class)->findAll();
        foreach ($addresses as $address) {
            $this->entityManager->remove($address);
        }
        $io->text(sprintf('Removed %d addresses', count($addresses)));
    }

    private function clearUsers(SymfonyStyle $io, bool $keepAdmin): void
    {
        $io->text('Clearing users...');
        
        if ($keepAdmin) {
            // Keep only admin users
            $users = $this->entityManager->getRepository(User::class)->findAll();
            $removedCount = 0;
            
            foreach ($users as $user) {
                if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                    $this->entityManager->remove($user);
                    $removedCount++;
                }
            }
            
            $io->text(sprintf('Removed %d non-admin users (admin users preserved)', $removedCount));
        } else {
            // Remove all users
            $users = $this->entityManager->getRepository(User::class)->findAll();
            foreach ($users as $user) {
                $this->entityManager->remove($user);
            }
            $io->text(sprintf('Removed %d users', count($users)));
        }
    }
} 