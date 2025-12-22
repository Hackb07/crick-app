<?php
/**
 * Admin Action Logger
 * 
 * Logs all admin actions for audit trail
 */

class ActionLogger {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Log an action
     * 
     * @param int $userId User ID
     * @param string $action Action type (create, update, delete, etc.)
     * @param string $resourceType Resource type (match, player, team, etc.)
     * @param int $resourceId Resource ID
     * @param array $details Additional details
     * @return bool Success
     */
    public function log($userId, $action, $resourceType, $resourceId = null, $details = []) {
        $sql = "INSERT INTO admin_action_logs (admin_id, action_type, resource_type, resource_id, reason, ip_address, timestamp) 
                VALUES (:admin_id, :action_type, :resource_type, :resource_id, :reason, :ip_address, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'admin_id' => $userId,
            'action_type' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'reason' => !empty($details) ? json_encode($details) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }
    
    /**
     * Get logs with filters
     * 
     * @param array $filters Filter options
     * @return array List of logs
     */
    public function getLogs($filters = []) {
        $sql = "SELECT al.*, u.username, u.full_name 
                FROM admin_action_logs al
                LEFT JOIN users u ON al.admin_id = u.user_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.admin_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['resource_type'])) {
            $sql .= " AND al.resource_type = :resource_type";
            $params['resource_type'] = $filters['resource_type'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action_type = :action";
            $params['action'] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND al.timestamp >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND al.timestamp <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY al.timestamp DESC LIMIT 1000";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

