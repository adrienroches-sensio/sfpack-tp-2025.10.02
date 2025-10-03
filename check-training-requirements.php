#!/usr/bin/env php
<?php

/**
 * Minimal, PSR-ish, Windows-friendly.
 * Requires Symfony CLI presence via SYMFONY_DOCKER_ENV (simple check, as requested).
 */
function ensureSymfonyBinary(): string
{
    if (getenv('SYMFONY_DOCKER_ENV') === false) {
        throw new RuntimeException(
            "Symfony CLI not detected (SYMFONY_DOCKER_ENV is missing). " .
            "Run via 'symfony php " . basename(__FILE__) . "' or ensure the Symfony context is present."
        );
    }

    return $_SERVER['_'] ?? 'symfony';
}

function checkPhpVersion(): void
{
    $requiredVersion = '8.2.0';
    if (version_compare(PHP_VERSION, $requiredVersion, '<')) {
        throw new RuntimeException(sprintf(
            "Incompatible PHP version.\n  * Required : %s+\n  * Current  : %s",
            $requiredVersion,
            PHP_VERSION
        ));
    }
}

function checkExtensions(): void
{
    $loaded = array_map('strtolower', get_loaded_extensions());

    // Your required list
    $required = [
        'curl',
        'json',
        'mbstring',
        'xml',
        'intl',
        'sodium',
        'zip',
        ['pcov', 'xdebug'],
        'PDO',
        'pdo_sqlite',
    ];

    /** @var list<string> $missingSingles */
    $missingSingles = [];

    /** @var list<list<string>> $missingAltGroups */
    $missingAltGroups = [];

    foreach ($required as $ext) {
        if (is_array($ext)) {
            $alts = array_map('strtolower', $ext);
            $allMissing = true;
            foreach ($alts as $a) {
                if (in_array($a, $loaded, true)) {
                    $allMissing = false;
                    break;
                }
            }
            if ($allMissing) {
                $missingAltGroups[] = $alts;
            }
            continue;
        }

        $name = strtolower($ext);
        if (!in_array($name, $loaded, true)) {
            $missingSingles[] = $name;
        }
    }

    if ($missingSingles === [] && $missingAltGroups === []) {
        return;
    }

    $details = [];
    if ($missingSingles !== []) {
        $scan = checkExtensionsInIni($missingSingles);
        foreach ($missingSingles as $ext) {
            $details[] = sprintf("%s (status: %s)", $ext, $scan[$ext] ?? 'unknown');
        }
    }

    foreach ($missingAltGroups as $group) {
        $scan = checkExtensionsInIni($group);
        $parts = [];
        foreach ($group as $ext) {
            $parts[] = sprintf("%s (%s)", $ext, $scan[$ext] ?? 'unknown');
        }
        $details[] = "One of: " . implode(', ', $parts);
    }

    throw new RuntimeException("Missing PHP extensions :\n * " . implode("\n * ", $details));
}

/**
 * @param list<string> $extensions
 *
 * @return array<string, 'not referenced'|'commented'|'enabled-but-not-loaded'> ext => status ("commented", "enabled-but-not-loaded", "not referenced")
 */
function checkExtensionsInIni(array $extensions): array
{
    $result = [];
    $files = getIniFiles();

    foreach ($extensions as $ext) {
        $status = 'not referenced';
        foreach ($files as $file) {
            $lines = @file($file, FILE_IGNORE_NEW_LINES) ?: [];
            foreach ($lines as $line) {
                if (1 === preg_match('/^\s*(?P<IsCommented>;)?\s*extension\s*=\s*(?P<ExtName>[^\s;#]+)/i', $line, $m)) {
                    $isCommented = $m['IsCommented'] === ';';
                    $val = normalizeExtName($m['ExtName']);
                    if ($val === normalizeExtName($ext)) {
                        $status = $isCommented === true ? 'commented' : 'enabled-but-not-loaded';
                        break 2;
                    }
                }
            }
        }
        $result[$ext] = $status;
    }

    return $result;
}

/** Like `php --ini`: main ini + scanned files */
function getIniFiles(): array
{
    $files = [];
    $main = php_ini_loaded_file();
    if (is_string($main) === true && $main !== '' && is_file($main) === true) {
        $files[] = $main;
    }
    $scanned = php_ini_scanned_files();
    if (is_string($scanned) === true && $scanned !== '') {
        foreach (preg_split('/[,\s;]+/', $scanned, -1, PREG_SPLIT_NO_EMPTY) as $p) {
            if (is_file($p) === true) {
                $files[] = $p;
            }
        }
    }
    return array_values(array_unique($files));
}

function normalizeExtName(string $value): string
{
    $v = trim($value, " \t\n\r\0\x0B\"'");
    $base = basename($v);
    $base = preg_replace('/^php_/i', '', $base);
    $base = preg_replace('/\.(dll|so)$/i', '', $base);
    return strtolower($base);
}

function checkIniSettings(): void
{
    $getBytes = static function (string $size): int {
        $size = trim($size);
        preg_match('/(?P<value>\d+)\s*(?P<metric>[a-zA-Z]*)/', $size, $m);
        $value = (int)($m['value'] ?? 0);
        $metric = isset($m['metric']) ? strtolower($m['metric']) : 'b';
        $mul = match ($metric) {
            'k','kb' => 1024,
            'm','mb' => 1024 ** 2,
            'g','gb' => 1024 ** 3,
            't','tb' => 1024 ** 4,
            default  => 1
        };
        return $value * $mul;
    };

    $rules = [
        'memory_limit'       => ['128M', fn(string $v, string $exp): bool => $v === '-1' || $getBytes($v) >= $getBytes($exp)],
        'max_execution_time' => ['300',  fn(string $v, string $exp): bool => $v === '0' || (int)$v >= (int)$exp],
    ];

    foreach ($rules as $key => [$expected, $assert]) {
        $current = (string)ini_get($key);
        if (!$assert($current, $expected)) {
            throw new RuntimeException(sprintf(
                "Non suitable PHP configuration.\nParameter : %s\nExpected  : %s\nCurrent   : %s",
                $key, $expected, $current
            ));
        }
    }
}

function checkRequirements(): void
{
    // Hardcode via Symfony CLI as requested
    $output = [];
    $code = 0;

    $symfonyBinary = ensureSymfonyBinary();

    exec("{$symfonyBinary} local:check:requirements 2>&1", $output, $code);
    if ($code !== 0) {
        throw new RuntimeException("Symfony's requirements are not met.\n" . implode("\n", $output));
    }
}

function checkGithubStatus(): void
{
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $json = @file_get_contents('https://www.githubstatus.com/api/v2/status.json', false, $ctx);
    if ($json === false) {
        throw new RuntimeException("Cannot access GitHub status.");
    }

    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['status']['indicator'])) {
        throw new RuntimeException("Invalid GitHub status response.");
    }

    if (!in_array($data['status']['indicator'], ['none','minor'], true)) {
        throw new RuntimeException("GitHub is having issues: " . ($data['status']['description'] ?? 'unknown'));
    }
}

function checkPackagistStatus(): void
{
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true, 'method' => 'HEAD']]);
    $headers = @get_headers('https://packagist.org', true, $ctx);
    if ($headers === false || !isset($headers[0])) {
        throw new RuntimeException("Cannot access Packagist.");
    }
    $status = (string)$headers[0];
    if (!str_contains($status, '200') && !str_contains($status, '301') && !str_contains($status, '302')) {
        throw new RuntimeException("Packagist is not accessible. Status: " . $status);
    }
}

function checkComposer(): void
{
    $output = [];
    $code = 0;

    $symfonyBinary = ensureSymfonyBinary();

    exec("{$symfonyBinary} composer diagnose 2>&1", $output, $code);
    if ($code !== 0) {
        throw new RuntimeException("Composer's requirements are not met.\n" . implode("\n", $output));
    }

    $text = implode("\n", $output);
    $m = null;
    preg_match('#\nComposer version\s*:\s*(?P<version>[^\n]+)#', $text, $m);
    $version = $m['version'] ?? null;
    $required = '2.4.0';
    if ($version === null || version_compare($version, $required, '<')) {
        throw new RuntimeException(sprintf(
            "Incompatible Composer version.\n  * Required : %s+\n  * Current  : %s",
            $required,
            $version ?? 'unknown'
        ));
    }
}

/* ------------------ Main ------------------ */

$errors = [];

try {
    ensureSymfonyBinary();
} catch (\Throwable $e) {
    $errors[] = $e;
}

// Steps (kept short)
$steps = [
        'checkPhpVersion',
        'checkExtensions',
        'checkIniSettings',
        'checkRequirements',
        'checkGithubStatus',
        'checkComposer',
        'checkPackagistStatus',
];

foreach ($steps as $step) {
    try {
        if (function_exists($step) !== true) {
            throw new LogicException(sprintf("Function %s does not exist", $step));
        }
        $step();
    } catch (\Throwable $e) {
        $errors[] = $e;
    }
}

if ($errors !== []) {
    echo "Update your local setup according to the following errors\n";
    echo "=========================================================\n\n";
    foreach ($errors as $e) {
        echo $e->getMessage() . "\n";
    }
    exit(1);
}

echo "All checks were successful!\n";
exit(0);
