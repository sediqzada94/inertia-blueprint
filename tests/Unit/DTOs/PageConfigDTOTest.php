<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\DTOs;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;

class PageConfigDTOTest extends TestCase
{
    public function test_creates_from_array_with_valid_data(): void
    {
        $data = [
            'model' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'content', 'type' => 'text', 'inputType' => 'textarea'],
            ],
            'pages' => ['index', 'create', 'edit'],
            'routes' => ['index' => 'posts.index'],
        ];

        $dto = PageConfigDTO::fromArray($data);

        $this->assertEquals('Post', $dto->model);
        $this->assertCount(2, $dto->fields);
        $this->assertInstanceOf(FieldConfigDTO::class, $dto->fields[0]);
        $this->assertEquals(['index', 'create', 'edit'], $dto->pages);
        $this->assertEquals(['index' => 'posts.index'], $dto->routes);
    }

    public function test_creates_from_array_without_routes(): void
    {
        $data = [
            'model' => 'User',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text'],
            ],
            'pages' => ['index'],
        ];

        $dto = PageConfigDTO::fromArray($data);

        $this->assertEquals('User', $dto->model);
        $this->assertNull($dto->routes);
    }

    public function test_handles_empty_fields_array(): void
    {
        $data = [
            'model' => 'Post',
            'fields' => [],
            'pages' => ['index'],
        ];

        $dto = PageConfigDTO::fromArray($data);

        $this->assertEquals('Post', $dto->model);
        $this->assertEmpty($dto->fields);
        $this->assertEquals(['index'], $dto->pages);
    }

    public function test_transforms_field_arrays_to_dtos(): void
    {
        $data = [
            'model' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'content', 'type' => 'text', 'inputType' => 'textarea'],
            ],
            'pages' => ['index'],
        ];

        $dto = PageConfigDTO::fromArray($data);

        $this->assertContainsOnlyInstancesOf(FieldConfigDTO::class, $dto->fields);
        $this->assertEquals('title', $dto->fields[0]->name);
        $this->assertEquals('content', $dto->fields[1]->name);
    }
}
