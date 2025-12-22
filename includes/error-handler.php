<?php
/**
 * Enhanced Error Handling
 * 
 * Provides consistent error handling across the application.
 * Eliminates duplicate error handling code.
 */

/**
 * Handle API error response
 * 
 * Provides consistent error response format for API endpoints.
 * 
 * @param Exception|Error|string $error Error object or message
 * @param int $statusCode HTTP status code (default: 500)
 * @param array $context Additional context data
 * @return void Exits with JSON response
 */
function handleApiError($error, $statusCode = 500, array $context = []) {
    // Clear output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Determine error message
    $message = 'Internal server error';
    $details = [];
    
    if ($error instanceof Exception || $error instanceof Error) {
        $message = $error->getMessage();
        $details = [
            'file' => basename($error->getFile()),
            'line' => $error->getLine(),
            'type' => get_class($error)
        ];
        
        // Add stack trace in debug mode only
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $details['trace'] = $error->getTraceAsString();
        }
        
        // Log error with full details
        error_log(sprintf(
            'API Error: %s in %s:%d | Context: %s | Trace: %s',
            $message,
            $error->getFile(),
            $error->getLine(),
            json_encode($context),
            $error->getTraceAsString()
        ));
    } elseif (is_string($error)) {
        $message = $error;
        error_log('API Error: ' . $message . ' | Context: ' . json_encode($context));
    }
    
    // Merge context into details
    if (!empty($context)) {
        $details = array_merge($details, $context);
    }
    
    // Build response
    $response = [
        'success' => false,
        'error' => $message
    ];
    
    if (!empty($details)) {
        $response['details'] = $details;
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Handle validation errors
 * 
 * Returns consistent validation error response.
 * 
 * @param array $errors Array of error messages
 * @param int $statusCode HTTP status code (default: 400)
 * @return void Exits with JSON response
 */
function handleValidationError(array $errors, $statusCode = 400) {
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Wrap API endpoint with error handling
 * 
 * Catches all exceptions and errors, providing consistent error responses.
 * 
 * @param callable $callback Function to execute
 * @param array $context Additional context for error logging
 * @return mixed Return value of callback
 */
function withErrorHandling(callable $callback, array $context = []) {
    try {
        return $callback();
    } catch (PDOException $e) {
        handleApiError($e, 500, array_merge($context, ['type' => 'database']));
    } catch (InvalidArgumentException $e) {
        handleApiError($e, 400, array_merge($context, ['type' => 'validation']));
    } catch (UnauthorizedException $e) {
        handleApiError($e, 401, array_merge($context, ['type' => 'authorization']));
    } catch (NotFoundException $e) {
        handleApiError($e, 404, array_merge($context, ['type' => 'not_found']));
    } catch (Exception $e) {
        handleApiError($e, 500, $context);
    } catch (Error $e) {
        handleApiError($e, 500, array_merge($context, ['type' => 'fatal']));
    }
}

/**
 * Custom exception classes for better error handling
 */
class UnauthorizedException extends Exception {}
class NotFoundException extends Exception {}

/**
 * Safe database query execution
 * 
 * Executes query with error handling and logging.
 * 
 * @param PDO $db Database connection
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return PDOStatement Executed statement
 * @throws PDOException If query fails
 */
function safeQuery(PDO $db, string $sql, array $params = []): PDOStatement {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log(sprintf(
            'Database query failed: %s | SQL: %s | Params: %s | Error: %s',
            $e->getMessage(),
            $sql,
            json_encode($params),
            $e->getTraceAsString()
        ));
        throw $e;
    }
}

