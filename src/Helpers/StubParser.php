<?php

namespace Sediqzada\InertiaBlueprint\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StubParser
{
    /**
     * @param  array<string, string>  $replacements
     */
    public static function parse(string $stubPath, array $replacements = []): string
    {
        if (! File::exists($stubPath)) {
            throw new \InvalidArgumentException("Stub file not found at path: $stubPath");
        }

        $stub = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $stub = Str::of($stub)->replace('{{'.Str::of($key)->upper().'}}', $value)->toString();
        }

        return $stub;
    }
}
