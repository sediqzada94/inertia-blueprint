<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Support;

use PHPUnit\Framework\MockObject\MockObject;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\CreatePageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\EditPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\IndexPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\ViewPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Support\PageGeneratorResolver;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class PageGeneratorResolverTest extends TestCase
{
    private PageConfigDTO $config;

    private PageGeneratorService&MockObject $pageGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new PageConfigDTO(
            model: 'Post',
            fields: [
                new FieldConfigDTO('title', 'string', 'text'),
            ],
            pages: ['index', 'create', 'edit', 'view']
        );

        $this->pageGenerator = $this->createMock(PageGeneratorService::class);
    }

    public function test_resolves_create_page_generator(): void
    {
        $generator = PageGeneratorResolver::resolve('create', $this->config, $this->pageGenerator);

        $this->assertInstanceOf(CreatePageGenerator::class, $generator);
    }

    public function test_resolves_edit_page_generator(): void
    {
        $generator = PageGeneratorResolver::resolve('edit', $this->config, $this->pageGenerator);

        $this->assertInstanceOf(EditPageGenerator::class, $generator);
    }

    public function test_resolves_index_page_generator(): void
    {
        $generator = PageGeneratorResolver::resolve('index', $this->config, $this->pageGenerator);

        $this->assertInstanceOf(IndexPageGenerator::class, $generator);
    }

    public function test_resolves_view_page_generator(): void
    {
        $generator = PageGeneratorResolver::resolve('view', $this->config, $this->pageGenerator);

        $this->assertInstanceOf(ViewPageGenerator::class, $generator);
    }

    public function test_throws_exception_for_unknown_page_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown page type: unknown');

        PageGeneratorResolver::resolve('unknown', $this->config, $this->pageGenerator);
    }
}
