<?php
/**
 * SoloLeveling Hostinger Deployment Helper Script
 * 
 * Access this file via browser to resolve the 500 error and configure the application:
 * https://sololeveling.io/deploy.php?token=sololeveling_deploy_2026
 */

// Define a secret security token
$secret_token = 'sololeveling_deploy_2026';

if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    header('HTTP/1.0 403 Forbidden');
    die('Unauthorized access.');
}

// Set time limit to 5 minutes
set_time_limit(300);

echo "<!DOCTYPE html>
<html>
<head>
    <title>SoloLeveling Deployment Helper</title>
    <style>
        body { background: #0f172a; color: #cbd5e1; font-family: monospace; padding: 20px; line-height: 1.5; }
        pre { background: #1e293b; padding: 15px; border-radius: 8px; border: 1px solid #334155; overflow-x: auto; color: #38bdf8; }
        h2 { color: #f43f5e; font-family: sans-serif; }
        .success { color: #4ade80; }
        .info { color: #38bdf8; }
        .cmd { color: #fb7185; }
    </style>
</head>
<body>
<h2>System Evolution & Deployment Panel</h2>
<hr style='border: 1px solid #334155;'>";

function run_command($cmd) {
    echo "<strong>Executing:</strong> <span class='cmd'>$cmd</span>\n";
    flush();
    if (function_exists('ob_flush')) {
        @ob_flush();
    }
    
    $output = [];
    $return_var = 0;
    exec($cmd . ' 2>&1', $output, $return_var);
    
    echo htmlspecialchars(implode("\n", $output)) . "\n";
    echo "<strong>Exit Code:</strong> $return_var\n\n";
    flush();
    if (function_exists('ob_flush')) {
        @ob_flush();
    }
    return $return_var;
}

echo "<pre>";

// Step 1: Ensure .env file exists
$env_path = dirname(__DIR__) . '/.env';
$env_example_path = dirname(__DIR__) . '/.env.example';

if (!file_exists($env_path)) {
    if (file_exists($env_example_path)) {
        echo "<span class='info'>[INFO] .env file not found. Copying .env.example to .env...</span>\n";
        if (copy($env_example_path, $env_path)) {
            echo "<span class='success'>[SUCCESS] Created .env file successfully.</span>\n\n";
        } else {
            echo "ERROR: Failed to copy .env.example to .env.\n\n";
        }
    } else {
        echo "ERROR: .env.example not found at $env_example_path.\n\n";
    }
} else {
    echo "<span class='info'>[INFO] .env file already exists.</span>\n\n";
}

// Step 2: Detect PHP version and locate correct PHP CLI executable
// On Hostinger, the default 'php' command in exec() might use an older CLI version.
// We test running php -v, and if needed, try to find a modern PHP path.
$php_cmd = 'php';
$php_version_output = [];
exec('php -r "echo PHP_VERSION;" 2>&1', $php_version_output);
$php_version = isset($php_version_output[0]) ? $php_version_output[0] : 'unknown';
echo "Default CLI PHP Version: $php_version\n";

if ($php_version !== 'unknown' && version_compare($php_version, '8.2.0', '<')) {
    echo "<span class='info'>[INFO] Default PHP CLI version ($php_version) is below 8.2. Trying to locate Hostinger PHP 8.2 or 8.3 binary...</span>\n";
    // Common Hostinger PHP CLI binary paths
    $possible_php_paths = [
        '/usr/bin/php8.2',
        '/usr/bin/php8.3',
        '/opt/alt/php82/usr/bin/php',
        '/opt/alt/php83/usr/bin/php',
        '/usr/local/bin/php'
    ];
    
    foreach ($possible_php_paths as $path) {
        $ver_out = [];
        @exec("$path -r \"echo PHP_VERSION;\" 2>&1", $ver_out);
        if (isset($ver_out[0]) && version_compare($ver_out[0], '8.2.0', '>=')) {
            $php_cmd = $path;
            echo "Found suitable PHP CLI binary: $php_cmd (Version: {$ver_out[0]})\n";
            break;
        }
    }
}

// Step 3: Run key:generate if APP_KEY is empty or missing
// Read the .env file to see if APP_KEY is set
$env_contents = file_exists($env_path) ? file_get_contents($env_path) : '';
if (!preg_match('/^APP_KEY=base64:[a-zA-Z0-9+\/={4}]+/m', $env_contents)) {
    echo "<span class='info'>[INFO] APP_KEY is empty or invalid. Generating application encryption key...</span>\n";
    run_command("$php_cmd ../artisan key:generate --force");
} else {
    echo "<span class='info'>[INFO] APP_KEY is already specified in .env.</span>\n\n";
}

// Step 4: Run database migrations
if (isset($_GET['migrate']) && ($_GET['migrate'] === 'true' || $_GET['migrate'] === '1')) {
    echo "<span class='info'>[INFO] Running database migrations...</span>\n";
    run_command("$php_cmd ../artisan migrate --force");
} else {
    echo "Skipping migrations (add &migrate=true to run)\n\n";
}

// Step 5: Clear and optimize application cache
echo "<span class='info'>[INFO] Clearing and optimizing caches...</span>\n";
run_command("$php_cmd ../artisan optimize:clear");
run_command("$php_cmd ../artisan optimize");

echo "<span class='success'><h3>Evolution Complete! System Online.</h3></span>";
echo "</pre>
</body>
</html>";
