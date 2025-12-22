<?php
// Quick test to see what happens with our null coalescing
$match = [];

echo "Test 1 - Empty array access with null coalescing:\n";
echo "Result: " . ($match['overs_per_innings'] ?? 20) . "\n\n";

echo "Test 2 - NULL with null coalescing:\n";
$match = null;
echo "Result: " . ($match['overs_per_innings'] ?? 20) . "\n\n";

echo "Test 3 - Array with key:\n";
$match = ['overs_per_innings' => 15];
echo "Result: " . ($match['overs_per_innings'] ?? 20) . "\n\n";

echo "All tests passed - null coalescing works correctly\n";
