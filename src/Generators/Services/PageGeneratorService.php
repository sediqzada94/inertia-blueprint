<?php

namespace Sediqzada\InertiaBlueprint\Generators\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PageGeneratorService
{
    public function readStub(string $fileName): string
    {
        // First check for published stubs in the user's application
        $publishedPath = resource_path("inertia-blueprint-stubs/react/$fileName.stub");

        if (File::exists($publishedPath)) {
            return File::get($publishedPath);
        }

        // Fall back to package stubs
        $packagePath = __DIR__."/../../../stubs/react/$fileName.stub";

        if (! File::exists($packagePath)) {
            throw new \RuntimeException("Stub file not found in either published location ($publishedPath) or package location ($packagePath)");
        }

        return File::get($packagePath);
    }

    public function getOutputPath(string $model, string $page): string
    {
        return base_path("resources/js/Pages/{$model}/$page.tsx");
    }

    public function resolveRoute(?string $route, string $model, string $action): string
    {
        return $route ?? Str::of($model)->plural()->append('.'.$action)->lower()->toString();
    }

    public function writeToFile(string $outputPath, string $content): void
    {
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);
    }

    /**
     * @param  array<string,string>  $replacements
     */
    public function replacePlaceholders(array $replacements, string $stub): string
    {
        foreach ($replacements as $placeholder => $value) {
            $stub = Str::of($stub)
                ->replace($placeholder, $value)
                ->toString();
        }

        return $stub;
    }
}
