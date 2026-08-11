<?php
/**
 * SoloLeveling Hostinger Deployment Helper Script (Pure Native PHP Version)
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
        .error { color: #f87171; }
    </style>
</head>
<body>
<h2>System Evolution & Deployment Panel (Native mode)</h2>
<hr style='border: 1px solid #334155;'>";

echo "<pre>";

$project_root = dirname(__DIR__);
$env_path = $project_root . '/.env';
$env_example_path = $project_root . '/.env.example';

// Step 1: Ensure .env file exists
if (!file_exists($env_path)) {
    if (file_exists($env_example_path)) {
        echo "<span class='info'>[INFO] .env file not found. Copying .env.example to .env...</span>\n";
        if (copy($env_example_path, $env_path)) {
            echo "<span class='success'>[SUCCESS] Created .env file successfully.</span>\n\n";
        } else {
            echo "<span class='error'>[ERROR] Failed to copy .env.example to .env.</span>\n\n";
        }
    } else {
        echo "<span class='error'>[ERROR] .env.example not found at $env_example_path.</span>\n\n";
    }
} else {
    echo "<span class='info'>[INFO] .env file already exists.</span>\n\n";
}

// Step 2: Native Key Generation and .env update
if (file_exists($env_path)) {
    $env_contents = file_get_contents($env_path);
    
    // Check if APP_KEY is empty or missing
    $has_key = preg_match('/^APP_KEY=base64:[a-zA-Z0-9+\/={4}]+/m', $env_contents);
    
    if (!$has_key) {
        echo "<span class='info'>[INFO] APP_KEY is empty or invalid. Generating encryption key natively...</span>\n";
        
        // Generate a cryptographically secure key natively
        $new_key = 'base64:' . base64_encode(random_bytes(32));
        
        // Replace or insert the key in the .env file
        if (strpos($env_contents, 'APP_KEY=') !== false) {
            $env_contents = preg_replace('/^APP_KEY=.*$/m', "APP_KEY=" . $new_key, $env_contents);
        } else {
            $env_contents .= "\nAPP_KEY=" . $new_key . "\n";
        }
        
        if (file_put_contents($env_path, $env_contents) !== false) {
            echo "<span class='success'>[SUCCESS] Natively generated APP_KEY and updated .env file.</span>\n";
            echo "New Key: <span class='success'>$new_key</span>\n\n";
        } else {
            echo "<span class='error'>[ERROR] Failed to write key to .env file. Please check write permissions on the file.</span>\n\n";
        }
    } else {
        echo "<span class='info'>[INFO] APP_KEY is already specified in .env.</span>\n\n";
    }
}

// Step 3: Clear Configuration Caches Natively
// When Laravel is cached, it ignores .env modifications. Deleting these cached files forces a rebuild.
echo "<span class='info'>[INFO] Clearing configuration and route caches natively...</span>\n";
$cache_files = [
    $project_root . '/bootstrap/cache/config.php',
    $project_root . '/bootstrap/cache/routes-v7.php',
    $project_root . '/bootstrap/cache/services.php',
    $project_root . '/bootstrap/cache/packages.php'
];

foreach ($cache_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<span class='success'>[SUCCESS] Deleted cache file: " . basename($file) . "</span>\n";
        } else {
            echo "<span class='error'>[ERROR] Failed to delete cache file: " . basename($file) . "</span>\n";
        }
    } else {
        echo "[INFO] Cache file not present: " . basename($file) . " (Already clear)\n";
    }
}
echo "\n";

// Step 4: Boot Laravel natively and run migrations if requested
if (isset($_GET['migrate']) && ($_GET['migrate'] === 'true' || $_GET['migrate'] === '1')) {
    echo "<span class='info'>[INFO] Booting Laravel to execute migrations natively...</span>\n";
    
    $autoload_path = $project_root . '/vendor/autoload.php';
    $app_path = $project_root . '/bootstrap/app.php';
    
    if (file_exists($autoload_path) && file_exists($app_path)) {
        try {
            // Require autoload and app files
            require_once $autoload_path;
            $app = require_once $app_path;
            
            // Resolve the kernel and run the command
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            
            echo "Running: <span class='cmd'>php artisan migrate --force</span>\n";
            $status = $kernel->call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            echo "Exit Status: " . ($status === 0 ? "<span class='success'>0 (SUCCESS)</span>" : "<span class='error'>$status (FAILED)</span>") . "\n";
            echo "Output:\n" . htmlspecialchars($output) . "\n";
        } catch (\Exception $e) {
            echo "<span class='error'>[ERROR] Laravel migration execution failed: " . htmlspecialchars($e->getMessage()) . "</span>\n";
        }
    } else {
        echo "<span class='error'>[ERROR] Vendor autoload or bootstrap app files not found. Cannot run migrations.</span>\n";
    }
} else {
    echo "Skipping migrations (add &migrate=true to run)\n\n";
}

echo "<span class='success'><h3>Evolution Complete! System Online.</h3></span>";
echo "</pre>
</body>
</html>";
