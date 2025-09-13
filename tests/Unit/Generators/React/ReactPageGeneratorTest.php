<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React;

use InvalidArgumentException;
use Sediqzada\InertiaBlueprint\Generators\React\ReactPageGenerator;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class ReactPageGeneratorTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'model' => 'User',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'string',
                    'inputType' => 'text',
                ],
            ],
            'pages' => ['create'],
            'routes' => [],
        ];
    }

    public function test_can_be_instantiated_with_config(): void
    {
        $generator = new ReactPageGenerator($this->config);

        $this->assertInstanceOf(ReactPageGenerator::class, $generator);
    }

    public function test_generate_throws_exception_for_invalid_page_type(): void
    {
        $generator = new ReactPageGenerator($this->config);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page generator class [Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\InvalidPageGenerator] does not exist.');

        $generator->generate('invalid');
    }

    public function test_class_name_resolution(): void
    {
        // Test various page types to ensure proper class name resolution
        $pageTypes = ['create', 'edit', 'index', 'view'];

        foreach ($pageTypes as $pageType) {
            $expectedClass = 'Sediqzada\\InertiaBlueprint\\Generators\\React\\PageGenerators\\'.ucfirst($pageType).'PageGenerator';

            // Verify the class exists (this validates our naming convention)
            $this->assertTrue(
                class_exists($expectedClass),
                "Expected class {$expectedClass} should exist for page type '{$pageType}'"
            );
        }
    }

    public function test_generate_with_complex_config(): void
    {
        $complexConfig = [
            'model' => 'Product',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'string',
                    'inputType' => 'text',
                ],
                [
                    'name' => 'description',
                    'type' => 'text',
                    'inputType' => 'textarea',
                ],
                [
                    'name' => 'price',
                    'type' => 'number',
                    'inputType' => 'number',
                ],
            ],
            'pages' => ['create', 'edit', 'index', 'view'],
            'routes' => [
                'store' => 'products.store',
                'index' => 'products.index',
                'edit' => 'products.edit',
                'update' => 'products.update',
                'show' => 'products.show',
                'destroy' => 'products.destroy',
            ],
        ];

        $generator = new ReactPageGenerator($complexConfig);

        // Should handle complex configurations without errors during instantiation
        $this->assertInstanceOf(ReactPageGenerator::class, $generator);
    }

    public function test_generate_with_empty_config(): void
    {
        $emptyConfig = [];

        $generator = new ReactPageGenerator($emptyConfig);

        // Should still work with empty config (generators will handle validation)
        $this->assertInstanceOf(ReactPageGenerator::class, $generator);
    }
}
