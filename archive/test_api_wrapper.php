<?php
/**
 * Test Events API Wrapper
 * 
 * Simply forwards POST data to events.php to test if it accepts JSON
 */
 
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['PATH_INFO'] = '/api/v1/events';

// Mock Input
$input = json_encode([
    'match_id' => 123,
    'events' => [],
    'client_base_seq' => 0
]);

// Determine if we can include the file directly or need to curl it
// Direct include might clash with headers
echo "API Wrapper Test Ready";
?>
