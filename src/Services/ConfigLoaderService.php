<?php

namespace Sediqzada\InertiaBlueprint\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;

class ConfigLoaderService
{
    /**
     * @throws FileNotFoundException
     * @throws \InvalidArgumentException
     */
    public function load(string $path): PageConfigDTO
    {
        $this->validateFile($path);
        $content = $this->readFile($path);

        return PageConfigDTO::fromArray($content);
    }

    private function validateFile(string $path): void
    {
        if (! Str::of($path)->endsWith('.json')) {
            throw new \InvalidArgumentException('The file must be a JSON type');
        }

        if (! File::exists($path)) {
            throw new FileNotFoundException("File not found: $path");
        }
    }

    /**
     * Decode JSON to an associative array.
     *
     * @return array{
     *     model: string,
     *     fields: list<array{
     *         name: string,
     *         type: string,
     *         inputType: string,
     *         field_name?: string|null,
     *         options?: array<mixed>|null,
     *         valueField?: string|null,
     *         labelField?: string|null,
     *         searchable?: bool
     *     }>,
     *     pages: list<string>,
     *     routes?: array<string,string>
     * }
     *
     * @throws \RuntimeException
     */
    private function readFile(string $path): array
    {
        $content = json_decode(File::get($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: '.json_last_error_msg());
        }

        /**
         * @var array{
         *     model: string,
         *     fields: list<array{
         *         name: string,
         *         type: string,
         *         inputType: string,
         *         field_name?: string|null,
         *         options?: array<mixed>|null,
         *         valueField?: string|null,
         *         labelField?: string|null,
         *         searchable?: bool
         *     }>,
         *     pages: list<string>,
         *     routes?: array<string,string>
         * } $content
         */
        return $content;
    }
}
