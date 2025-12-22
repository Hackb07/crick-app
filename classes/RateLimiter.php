<?php
/**
 * Rate Limiting Implementation
 * 
 * Tracks API requests per user/IP to prevent abuse
 */

class RateLimiter {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Check if request is allowed
     * 
     * @param string $identifier User ID or IP address
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @return array ['allowed' => bool, 'remaining' => int, 'reset' => int]
     */
    public function check($identifier, $endpoint, $method = 'GET') {
        // Determine limits based on method
        $isWrite = in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']);
        
        // Check if identifier is numeric (user ID) or string (IP)
        $isAuthenticated = is_numeric($identifier);
        
        if ($isAuthenticated) {
            $perMinLimit = $isWrite ? RATE_LIMIT_AUTH_PER_MIN : RATE_LIMIT_AUTH_PER_MIN;
            $perHourLimit = $isWrite ? RATE_LIMIT_AUTH_PER_HOUR : RATE_LIMIT_AUTH_PER_HOUR;
        } else {
            $perMinLimit = RATE_LIMIT_ANONYMOUS_PER_MIN;
            $perHourLimit = RATE_LIMIT_ANONYMOUS_PER_HOUR;
        }
        
        $now = time();
        $minuteWindow = floor($now / 60) * 60;
        $hourWindow = floor($now / 3600) * 3600;
        
        // Check per-minute limit
        $minuteKey = $identifier . ':' . $endpoint . ':minute:' . $minuteWindow;
        $minuteCount = $this->getCount($minuteKey);
        
        if ($minuteCount >= $perMinLimit) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset' => $minuteWindow + 60,
                'limit' => $perMinLimit,
                'reason' => 'per_minute_limit_exceeded'
            ];
        }
        
        // Check per-hour limit
        $hourKey = $identifier . ':' . $endpoint . ':hour:' . $hourWindow;
        $hourCount = $this->getCount($hourKey);
        
        if ($hourCount >= $perHourLimit) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset' => $hourWindow + 3600,
                'limit' => $perHourLimit,
                'reason' => 'per_hour_limit_exceeded'
            ];
        }
        
        // Increment counters
        $this->increment($minuteKey, 60);
        $this->increment($hourKey, 3600);
        
        return [
            'allowed' => true,
            'remaining' => min($perMinLimit - $minuteCount - 1, $perHourLimit - $hourCount - 1),
            'reset' => $minuteWindow + 60,
            'limit' => $perMinLimit
        ];
    }
    
    /**
     * Get count for key
     * 
     * @param string $key Rate limit key
     * @return int Count
     */
    private function getCount($key) {
        $sql = "SELECT count FROM rate_limits 
                WHERE identifier = :key 
                AND expires_at > NOW() 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Increment counter
     * 
     * @param string $key Rate limit key
     * @param int $ttl Time to live in seconds
     */
    private function increment($key, $ttl) {
        $sql = "INSERT INTO rate_limits (identifier, endpoint, count, window_start, expires_at) 
                VALUES (:key, '', 1, NOW(), DATE_ADD(NOW(), INTERVAL :ttl SECOND))
                ON DUPLICATE KEY UPDATE count = count + 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'key' => $key,
            'ttl' => $ttl
        ]);
    }
    
    /**
     * Clean up expired rate limit entries
     */
    public function cleanup() {
        $sql = "DELETE FROM rate_limits WHERE expires_at < NOW()";
        $this->db->exec($sql);
    }
}

