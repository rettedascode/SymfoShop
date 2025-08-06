<?php

namespace App\Tests\Command;

use App\Command\SetupCommand;
use App\Service\ConfigurationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SetupCommandTest extends TestCase
{
    private SetupCommand $command;
    private ConfigurationService $mockConfigurationService;
    private ContainerInterface $mockContainer;

    protected function setUp(): void
    {
        $this->mockConfigurationService = $this->createMock(ConfigurationService::class);
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        
        $this->command = new SetupCommand($this->mockConfigurationService);
        $this->command->setContainer($this->mockContainer);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('app:setup', $this->command->getName());
    }

    public function testCommandDescription(): void
    {
        $this->assertStringContainsString('Initialize', $this->command->getDescription());
    }

    public function testCommandHelp(): void
    {
        $this->assertStringContainsString('setup', $this->command->getHelp());
    }

    public function testCommandOptions(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertTrue($definition->hasOption('skip-config'));
        $this->assertTrue($definition->hasOption('skip-sample-data'));
        $this->assertTrue($definition->hasOption('force'));
    }

    public function testExecuteWithDefaultOptions(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('initializeDefaults');

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with('doctrine')
            ->willReturn($this->createMockDoctrine());

        $commandTester = $this->createCommandTester();
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('SymfoShop setup completed successfully', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithSkipConfigOption(): void
    {
        $this->mockConfigurationService->expects($this->never())
            ->method('initializeDefaults');

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with('doctrine')
            ->willReturn($this->createMockDoctrine());

        $commandTester = $this->createCommandTester();
        $commandTester->execute(['--skip-config' => true]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Configuration initialization skipped', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithSkipSampleDataOption(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('initializeDefaults');

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with('doctrine')
            ->willReturn($this->createMockDoctrine());

        $commandTester = $this->createCommandTester();
        $commandTester->execute(['--skip-sample-data' => true]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Sample data creation skipped', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithForceOption(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('initializeDefaults');

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with('doctrine')
            ->willReturn($this->createMockDoctrine());

        $commandTester = $this->createCommandTester();
        $commandTester->execute(['--force' => true]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Force mode enabled', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithAllSkipOptions(): void
    {
        $this->mockConfigurationService->expects($this->never())
            ->method('initializeDefaults');

        $this->mockContainer->expects($this->never())
            ->method('get');

        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            '--skip-config' => true,
            '--skip-sample-data' => true
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Nothing to do', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithConfigurationServiceException(): void
    {
        $this->mockConfigurationService->expects($this->once())
            ->method('initializeDefaults')
            ->willThrowException(new \Exception('Configuration error'));

        $commandTester = $this->createCommandTester();
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Error during setup', $output);
        $this->assertEquals(1, $commandTester->getStatusCode());
    }

    private function createCommandTester(): CommandTester
    {
        $application = new Application();
        $application->add($this->command);
        
        return new CommandTester($this->command);
    }

    private function createMockDoctrine(): object
    {
        $mockEntityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $mockEntityManager->expects($this->any())
            ->method('persist')
            ->willReturnSelf();
        $mockEntityManager->expects($this->any())
            ->method('flush')
            ->willReturnSelf();

        $mockDoctrine = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $mockDoctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($mockEntityManager);

        return $mockDoctrine;
    }
} 