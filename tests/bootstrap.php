<?php

// Use an installed Winter CMS for its real dependencies, without booting the
// application kernel, reading .env, or running migrations.
$cmsRoot = getenv('WINTER_CMS_PATH') ?: dirname(__DIR__, 4);
if (!is_file($cmsRoot . '/modules/system/tests/bootstrap/app.php')) {
    throw new RuntimeException('Set WINTER_CMS_PATH to an installed Winter CMS project root.');
}
define('PLATFORM_TEST_CMS_ROOT', $cmsRoot);
require $cmsRoot . '/modules/system/tests/bootstrap/app.php';

foreach ([
    'Model' => Winter\Storm\Database\Model::class,
    'Config' => Illuminate\Support\Facades\Config::class,
    'Event' => Illuminate\Support\Facades\Event::class,
    'Lang' => Illuminate\Support\Facades\Lang::class,
    'Log' => Illuminate\Support\Facades\Log::class,
    'Validator' => Illuminate\Support\Facades\Validator::class,
    'ValidationException' => Winter\Storm\Exception\ValidationException::class,
] as $alias => $class) {
    if (!class_exists($alias, false)) {
        class_alias($class, $alias);
    }
}
require __DIR__ . '/PlatformTestCase.php';
// Test this checkout, even when the host CMS has a different installed version.
require dirname(__DIR__) . '/models/Job.php';
