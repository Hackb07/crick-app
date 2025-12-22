<?php
/**
 * Run Test Data Generator
 * 
 * Usage: php run-test-data.php
 */

// Change to project root
chdir(__DIR__);

echo "\n";
echo "╔══════════════════════════════════════════╗\n";
echo "║   CricApp Test Data Generator           ║\n";
echo "║   Creating realistic test matches...     ║\n";
echo "╚══════════════════════════════════════════╝\n";
echo "\n";

// Run the seed script
require_once __DIR__ . '/database/seeds/create_test_matches.php';

echo "\n";
echo "╔══════════════════════════════════════════╗\n";
echo "║   ✅ Test Data Generation Complete!     ║\n";
echo "╚══════════════════════════════════════════╝\n";
echo "\n";
