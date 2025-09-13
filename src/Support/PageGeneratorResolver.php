<?php

namespace Sediqzada\InertiaBlueprint\Support;

use Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\CreatePageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\EditPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\IndexPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\ViewPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;

class PageGeneratorResolver
{
    public static function resolve(
        string $page,
        PageConfigDTO $config,
        PageGeneratorService $pageGenerator
    ): PageGeneratorInterface {
        return match ($page) {
            'create' => new CreatePageGenerator($config, $pageGenerator),
            'edit' => new EditPageGenerator($config, $pageGenerator),
            'index' => new IndexPageGenerator($config, $pageGenerator),
            'view' => new ViewPageGenerator($config, $pageGenerator),
            default => throw new \InvalidArgumentException("Unknown page type: $page")
        };
    }
}
