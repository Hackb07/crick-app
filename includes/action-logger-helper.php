<?php
/**
 * Action Logger Helper Functions
 * 
 * Convenience functions for logging admin actions
 */

if (!function_exists('logAction')) {
    /**
     * Log an admin action
     * 
     * @param string $action Action type
     * @param string $resourceType Resource type
     * @param int|null $resourceId Resource ID
     * @param array $details Additional details
     */
    function logAction($action, $resourceType, $resourceId = null, $details = []) {
        if (!function_exists('getUserId')) {
            return false;
        }
        
        $userId = getUserId();
        if (!$userId) {
            return false;
        }
        
        try {
            $logger = new ActionLogger();
            return $logger->log($userId, $action, $resourceType, $resourceId, $details);
        } catch (Exception $e) {
            error_log('Failed to log action: ' . $e->getMessage());
            return false;
        }
    }
}

