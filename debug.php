<?php
// Debug script to check environment variables

echo "<h2>Reddit RSS Debug Information</h2>";
echo "<pre>";

echo "=== Environment Variables ===\n";
echo "REDDIT_USER: " . (isset($_SERVER["REDDIT_USER"]) ? $_SERVER["REDDIT_USER"] : "NOT SET") . "\n";
echo "REDDIT_CLIENT_ID: " . (isset($_SERVER["REDDIT_CLIENT_ID"]) ? substr($_SERVER["REDDIT_CLIENT_ID"], 0, 5) . "..." : "NOT SET") . "\n";
echo "REDDIT_CLIENT_SECRET: " . (isset($_SERVER["REDDIT_CLIENT_SECRET"]) ? "SET (hidden)" : "NOT SET") . "\n\n";

echo "=== getenv() Check ===\n";
echo "REDDIT_USER (getenv): " . (getenv("REDDIT_USER") ?: "NOT SET") . "\n";
echo "REDDIT_CLIENT_ID (getenv): " . (getenv("REDDIT_CLIENT_ID") ? substr(getenv("REDDIT_CLIENT_ID"), 0, 5) . "..." : "NOT SET") . "\n";
echo "REDDIT_CLIENT_SECRET (getenv): " . (getenv("REDDIT_CLIENT_SECRET") ? "SET (hidden)" : "NOT SET") . "\n\n";

echo "=== $_ENV Check ===\n";
echo "REDDIT_USER (\$_ENV): " . (isset($_ENV["REDDIT_USER"]) ? $_ENV["REDDIT_USER"] : "NOT SET") . "\n";
echo "REDDIT_CLIENT_ID (\$_ENV): " . (isset($_ENV["REDDIT_CLIENT_ID"]) ? substr($_ENV["REDDIT_CLIENT_ID"], 0, 5) . "..." : "NOT SET") . "\n";
echo "REDDIT_CLIENT_SECRET (\$_ENV): " . (isset($_ENV["REDDIT_CLIENT_SECRET"]) ? "SET (hidden)" : "NOT SET") . "\n\n";

// Load config to check if constants are defined
include_once('config-default.php');

echo "=== After Loading Config ===\n";
echo "REDDIT_USER constant: " . (defined('REDDIT_USER') ? REDDIT_USER : "NOT DEFINED") . "\n";
echo "REDDIT_CLIENT_ID constant: " . (defined('REDDIT_CLIENT_ID') ? substr(REDDIT_CLIENT_ID, 0, 5) . "..." : "NOT DEFINED") . "\n";
echo "REDDIT_CLIENT_SECRET constant: " . (defined('REDDIT_CLIENT_SECRET') ? "SET (hidden)" : "NOT DEFINED") . "\n\n";

echo "=== Cache Directory Check ===\n";
echo "Current directory: " . getcwd() . "\n";
echo "Cache dir exists: " . (is_dir('cache') ? "YES" : "NO") . "\n";
echo "Cache dir writable: " . (is_writable('cache') || is_writable('.') ? "YES" : "NO") . "\n\n";

echo "=== PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "cURL enabled: " . (function_exists('curl_init') ? "YES" : "NO") . "\n";

echo "</pre>";
?>