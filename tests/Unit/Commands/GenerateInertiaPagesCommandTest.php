<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Commands;

use PHPUnit\Framework\MockObject\MockObject;
use Sediqzada\InertiaBlueprint\Commands\GenerateBlueprintPagesCommand;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Services\ConfigLoaderService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class GenerateInertiaPagesCommandTest extends TestCase
{
    private ConfigLoaderService&MockObject $configLoader;

    private PageGeneratorService&MockObject $pageGenerator;

    private GenerateBlueprintPagesCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configLoader = $this->createMock(ConfigLoaderService::class);
        $this->pageGenerator = $this->createMock(PageGeneratorService::class);
        $this->command = new GenerateBlueprintPagesCommand($this->configLoader, $this->pageGenerator);
    }

    public function test_command_has_correct_signature_and_description(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $signatureProperty = $reflection->getProperty('signature');
        $descriptionProperty = $reflection->getProperty('description');

        $this->assertEquals('blueprint:generate {file?}', $signatureProperty->getValue($this->command));
        $this->assertEquals('Generate Inertia.js pages from a JSON blueprint', $descriptionProperty->getValue($this->command));
    }

    public function test_command_can_be_instantiated(): void
    {
        $this->assertInstanceOf(GenerateBlueprintPagesCommand::class, $this->command);
    }

    public function test_command_accepts_dependencies(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(2, $constructor->getParameters());
    }
}
