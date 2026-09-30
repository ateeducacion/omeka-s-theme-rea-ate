<?php

declare(strict_types=1);

namespace ReaAteTest;

// Composer autoload (phpunit + dev tools).
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
}

// Lightweight PSR-4 autoloader for the theme helpers, Laminas stubs and tests.
spl_autoload_register(function (string $class): void {
    // Theme view helpers live in the repo `helper/` dir under OmekaTheme\Helper.
    if (strpos($class, 'OmekaTheme\\Helper\\') === 0) {
        $name = substr($class, strlen('OmekaTheme\\Helper\\'));
        $file = dirname(__DIR__) . '/helper/' . str_replace('\\', '/', $name) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
    // Minimal Laminas stubs used by the helpers (e.g. AbstractHelper).
    if (strpos($class, 'Laminas\\') === 0) {
        $file = __DIR__ . '/Stubs/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
    // Test classes/doubles under the ReaAteTest namespace.
    if (strpos($class, __NAMESPACE__ . '\\') === 0) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen(__NAMESPACE__ . '\\'))) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
