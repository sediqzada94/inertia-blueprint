<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\Services;

use Illuminate\Support\Facades\File;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class PageGeneratorServiceTest extends TestCase
{
    private PageGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PageGeneratorService;
    }

    public function test_reads_stub_files(): void
    {
        $stubContent = 'Test stub content with {{ placeholder }}';

        File::shouldReceive('exists')
            ->once()
            ->with(\Mockery::pattern('/.*stubs\/react\/Create\.stub$/'))
            ->andReturn(true);

        File::shouldReceive('get')
            ->once()
            ->with(\Mockery::pattern('/.*stubs\/react\/Create\.stub$/'))
            ->andReturn($stubContent);

        $result = $this->service->readStub('Create');

        $this->assertEquals($stubContent, $result);
    }

    public function test_throws_exception_for_missing_stub_files(): void
    {
        File::shouldReceive('exists')
            ->once()
            ->with(\Mockery::pattern('/.*inertia-blueprint-stubs\/react\/NonExistent\.stub$/'))
            ->andReturn(false);

        File::shouldReceive('exists')
            ->once()
            ->with(\Mockery::pattern('/.*stubs\/react\/NonExistent\.stub$/'))
            ->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stub file not found in either published location');

        $this->service->readStub('NonExistent');
    }

    public function test_generates_correct_output_paths(): void
    {
        $testCases = [
            ['Post', 'Create', 'resources/js/Pages/Post/Create.tsx'],
            ['User', 'Index', 'resources/js/Pages/User/Index.tsx'],
            ['Product', 'Edit', 'resources/js/Pages/Product/Edit.tsx'],
        ];

        foreach ($testCases as [$model, $page, $expectedPath]) {
            $result = $this->service->getOutputPath($model, $page);
            $this->assertEquals(base_path($expectedPath), $result);
        }
    }

    public function test_resolves_routes_correctly(): void
    {
        // Custom routes take precedence
        $this->assertEquals(
            'custom.posts.store',
            $this->service->resolveRoute('custom.posts.store', 'Post', 'store')
        );

        // Auto-generated routes
        $testCases = [
            ['Post', 'index', 'posts.index'],
            ['User', 'create', 'users.create'],
            ['BlogPost', 'edit', 'blogposts.edit'],
            ['Category', 'destroy', 'categories.destroy'],
        ];

        foreach ($testCases as [$model, $action, $expected]) {
            $result = $this->service->resolveRoute(null, $model, $action);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_writes_files_with_directory_creation(): void
    {
        $outputPath = '/path/to/output/file.tsx';
        $content = 'Generated file content';

        File::shouldReceive('ensureDirectoryExists')->with('/path/to/output')->once();
        File::shouldReceive('put')->with($outputPath, $content)->once();

        $this->service->writeToFile($outputPath, $content);
    }

    public function test_replaces_placeholders_in_templates(): void
    {
        $testCases = [
            // Basic replacement
            [
                'Hello {{ name }}!',
                ['{{ name }}' => 'World'],
                'Hello World!',
            ],
            // Multiple placeholders
            [
                'Hello {{ name }}, welcome to {{ app }}!',
                ['{{ name }}' => 'John', '{{ app }}' => 'Inertial Blueprint'],
                'Hello John, welcome to Inertial Blueprint!',
            ],
            // Repeated placeholders
            [
                'The {{ item }} is a {{ item }}.',
                ['{{ item }}' => 'widget'],
                'The widget is a widget.',
            ],
            // Missing placeholders (should remain unchanged)
            [
                'Hello {{ name }} and {{ missing }}!',
                ['{{ name }}' => 'John'],
                'Hello John and {{ missing }}!',
            ],
        ];

        foreach ($testCases as [$stub, $replacements, $expected]) {
            $result = $this->service->replacePlaceholders($replacements, $stub);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_handles_complex_template_content(): void
    {
        $stub = 'Component: {{ component }}';
        $complexReplacement = '<div className="test">Complex content with {{ nested }}</div>';
        $replacements = ['{{ component }}' => $complexReplacement];

        $result = $this->service->replacePlaceholders($replacements, $stub);

        $this->assertEquals('Component: <div className="test">Complex content with {{ nested }}</div>', $result);
    }

    public function test_handles_special_characters_and_code(): void
    {
        $stub = 'Code: {{ code }}';
        $replacements = ['{{ code }}' => 'const test = () => { return "hello"; };'];

        $result = $this->service->replacePlaceholders($replacements, $stub);

        $this->assertEquals('Code: const test = () => { return "hello"; };', $result);
    }
}
