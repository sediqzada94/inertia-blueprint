<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Commands;

use Illuminate\Support\Facades\File;
use Sediqzada\InertiaBlueprint\Commands\PublishStubsCommand;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class PublishStubsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_command_has_correct_signature(): void
    {
        $command = new PublishStubsCommand;
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $descriptionProperty = $reflection->getProperty('description');

        $this->assertEquals('blueprint:publish-stubs {--force : Overwrite existing stubs}', $signatureProperty->getValue($command));
        $this->assertEquals('Publish Inertia Blueprint stub files for customization', $descriptionProperty->getValue($command));
    }

    public function test_publishes_stubs_successfully_when_directory_does_not_exist(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(false);

        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->with($publishPath);

        File::shouldReceive('copyDirectory')
            ->once();

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['info', 'line'])
            ->getMock();

        $command->expects($this->once())
            ->method('info')
            ->with('✅ Inertia Blueprint stubs published successfully!');

        $command->expects($this->atLeast(1))
            ->method('line');

        $result = $command->handle();

        $this->assertEquals(0, $result);
    }

    public function test_publishes_stubs_with_force_flag(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(true);

        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->with($publishPath);

        File::shouldReceive('copyDirectory')
            ->once();

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['option', 'info', 'line'])
            ->getMock();

        $command->expects($this->once())
            ->method('option')
            ->with('force')
            ->willReturn(true);

        $command->expects($this->once())
            ->method('info')
            ->with('✅ Inertia Blueprint stubs published successfully!');

        $result = $command->handle();

        $this->assertEquals(0, $result);
    }

    public function test_asks_for_confirmation_when_stubs_exist_without_force(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(true);

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['option', 'confirm', 'info'])
            ->getMock();

        $command->expects($this->once())
            ->method('option')
            ->with('force')
            ->willReturn(false);

        $command->expects($this->once())
            ->method('confirm')
            ->with('Stubs already exist. Do you want to overwrite them?')
            ->willReturn(false);

        $command->expects($this->once())
            ->method('info')
            ->with('Publishing cancelled.');

        $result = $command->handle();

        $this->assertEquals(0, $result);
    }

    public function test_publishes_when_user_confirms_overwrite(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(true);

        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->with($publishPath);

        File::shouldReceive('copyDirectory')
            ->once();

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['option', 'confirm', 'info', 'line'])
            ->getMock();

        $command->expects($this->once())
            ->method('option')
            ->with('force')
            ->willReturn(false);

        $command->expects($this->once())
            ->method('confirm')
            ->with('Stubs already exist. Do you want to overwrite them?')
            ->willReturn(true);

        $command->expects($this->once())
            ->method('info')
            ->with('✅ Inertia Blueprint stubs published successfully!');

        $result = $command->handle();

        $this->assertEquals(0, $result);
    }

    public function test_handles_exception_during_publishing(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(false);

        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->with($publishPath)
            ->andThrow(new \Exception('Permission denied'));

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['option', 'error'])
            ->getMock();

        $command->expects($this->any())
            ->method('option')
            ->with('force')
            ->willReturn(false);

        $command->expects($this->once())
            ->method('error')
            ->with('Failed to publish stubs: Permission denied');

        $result = $command->handle();

        $this->assertEquals(1, $result);
    }

    public function test_handles_exception_during_copy_directory(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(false);

        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->with($publishPath);

        File::shouldReceive('copyDirectory')
            ->once()
            ->andThrow(new \Exception('Failed to copy files'));

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['option', 'error'])
            ->getMock();

        $command->expects($this->any())
            ->method('option')
            ->with('force')
            ->willReturn(false);

        $command->expects($this->once())
            ->method('error')
            ->with('Failed to publish stubs: Failed to copy files');

        $result = $command->handle();

        $this->assertEquals(1, $result);
    }

    public function test_command_can_be_instantiated(): void
    {
        $command = new PublishStubsCommand;

        $this->assertInstanceOf(PublishStubsCommand::class, $command);
    }

    public function test_command_properties_are_set_correctly(): void
    {
        $command = new PublishStubsCommand;
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $descriptionProperty = $reflection->getProperty('description');

        $signature = $signatureProperty->getValue($command);
        $description = $descriptionProperty->getValue($command);

        $this->assertIsString($signature);
        $this->assertIsString($description);

        $this->assertStringContainsString('blueprint:publish-stubs', $signature);
        $this->assertStringContainsString('--force', $signature);
        $this->assertStringContainsString('Overwrite existing stubs', $signature);
        $this->assertStringContainsString('Publish Inertia Blueprint stub files for customization', $description);
    }

    public function test_force_option_bypasses_confirmation(): void
    {
        $publishPath = resource_path('inertia-blueprint-stubs');

        File::shouldReceive('exists')
            ->once()
            ->with($publishPath)
            ->andReturn(true);

        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->with($publishPath);

        File::shouldReceive('copyDirectory')
            ->once();

        $command = $this->getMockBuilder(PublishStubsCommand::class)
            ->onlyMethods(['option', 'confirm', 'info', 'line'])
            ->getMock();

        $command->expects($this->once())
            ->method('option')
            ->with('force')
            ->willReturn(true);

        // With --force, it should not ask for confirmation
        $command->expects($this->never())
            ->method('confirm');

        $command->expects($this->once())
            ->method('info')
            ->with('✅ Inertia Blueprint stubs published successfully!');

        $result = $command->handle();

        $this->assertEquals(0, $result);
    }
}
