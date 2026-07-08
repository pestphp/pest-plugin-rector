<?php

declare(strict_types=1);

use Rector\Testing\Fixture\FixtureFileFinder;

beforeAll(function (): void {
    self::$configFilePath = __DIR__ . '/config/configured_rule.php';
});

test('', function (string $filePath): void {
    $directory = dirname($filePath);

    while (strtolower(basename($directory)) !== 'tests') {
        $directory = dirname($directory);
    }

    $pestFixture = $directory . '/Pest.php.fixture';
    $pestFile = $directory . '/Pest.php';

    if (is_file($pestFixture)) {
        copy($pestFixture, $pestFile);
    }

    try {
        $this->doTestFile($filePath);
    } finally {
        if (is_file($pestFile)) {
            unlink($pestFile);
        }
    }
})->with(
    fn (): Iterator => FixtureFileFinder::yieldDirectory(__DIR__ . '/Fixture')
);
