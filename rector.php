<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(typeDeclarations: true)
    ->withPreparedSets(deadCode: true)
    ->withPreparedSets(codeQuality: true);
