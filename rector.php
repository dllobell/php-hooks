<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests'])
    ->withRootFiles()
    ->withPhpSets(php84: true)
    ->withSkip([])
;
