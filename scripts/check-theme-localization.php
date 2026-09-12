<?php
/**
 * Static localization check for the active EdTech theme.
 *
 * Usage: php scripts/check-theme-localization.php
 */

declare(strict_types=1);

$themeRoot = dirname(__DIR__) . '/theme/edtech';
$languageFiles = [
    'en' => $themeRoot . '/lang/en/theme_edtech.php',
    'ar' => $themeRoot . '/lang/ar/theme_edtech.php',
];

function extract_language_keys(string $path): array {
    $contents = file_get_contents($path);
    if ($contents === false) {
        fwrite(STDERR, "Unable to read {$path}\n");
        exit(1);
    }

    preg_match_all("/\\\$string\\[['\"]([^'\"]+)['\"]\\]/", $contents, $matches);
    return array_values(array_unique($matches[1]));
}

$keys = [];
foreach ($languageFiles as $language => $path) {
    $keys[$language] = extract_language_keys($path);
}

$englishOnly = array_diff($keys['en'], $keys['ar']);
$arabicOnly = array_diff($keys['ar'], $keys['en']);
$errors = [];

if ($englishOnly) {
    $errors[] = 'Missing from Arabic: ' . implode(', ', $englishOnly);
}
if ($arabicOnly) {
    $errors[] = 'Missing from English: ' . implode(', ', $arabicOnly);
}

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($themeRoot, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }
    if (str_ends_with($file->getFilename(), '.mustache') || $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

$used = [];
foreach ($files as $path) {
    $contents = file_get_contents($path);
    if ($contents === false) {
        continue;
    }

    preg_match_all(
        "/get_string\\(\\s*['\"]([^'\"]+)['\"]\\s*,\\s*['\"]theme_edtech['\"]/",
        $contents,
        $phpMatches
    );
    $used = array_merge($used, $phpMatches[1] ?? []);

    preg_match_all('/\\{\\{#str\\}\\}\\s*([a-z0-9_]+)\\s*,\\s*theme_edtech/', $contents, $mustacheMatches);
    $used = array_merge($used, $mustacheMatches[1] ?? []);
}

$used = array_values(array_unique($used));
foreach ($used as $key) {
    if (strpos($key, '{') !== false) {
        continue;
    }
    if (!in_array($key, $keys['en'], true) || !in_array($key, $keys['ar'], true)) {
        $errors[] = "Runtime key '{$key}' is not defined in both language packs";
    }
}

if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

printf(
    "Localization check passed: %d shared keys, %d runtime theme keys.\n",
    count($keys['en']),
    count($used)
);
