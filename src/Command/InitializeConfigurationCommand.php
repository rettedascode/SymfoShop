<?php

namespace App\Command;

use App\Service\ConfigurationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize-configuration',
    description: 'Initialize default configuration values',
)]
class InitializeConfigurationCommand extends Command
{
    public function __construct(
        private ConfigurationService $configurationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initialize Configuration');
        $io->text('Setting up default configuration values...');

        try {
            $this->configurationService->initializeDefaults();
            
            $io->success('Configuration initialized successfully!');
            $io->text('Default configuration values have been set up.');
            $io->text('You can now modify these values through the admin interface.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to initialize configuration: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 