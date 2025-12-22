<?php
/**
 * API Error Wrapper
 * 
 * Sets up error handling for API endpoints to ensure JSON responses.
 * This file is required by all API endpoints to handle fatal errors and exceptions gracefully.
 */

// Ensure we don't output HTML errors
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set error handler to return JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Don't handle suppressed errors
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Log the error
    error_log("API Error [$errno]: $errstr in $errfile on line $errline");
    
    // Return JSON error
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal Server Error',
            'debug' => (defined('DEBUG_MODE') && DEBUG_MODE) ? "$errstr in $errfile:$errline" : null
        ]);
    }
    exit;
});

// Set exception handler
set_exception_handler(function($e) {
    // Log the exception
    error_log("API Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    
    // Return JSON error
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal Server Error: ' . $e->getMessage()
        ]);
    }
    exit;
});

// Handle fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        // Log the error
        error_log("API Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
        
        // Return JSON error if headers not sent
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Fatal Error',
                'debug' => (defined('DEBUG_MODE') && DEBUG_MODE) ? $error : null
            ]);
        }
    }
});
