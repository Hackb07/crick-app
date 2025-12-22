<?php
/**
 * JWT (JSON Web Token) Implementation
 * 
 * Custom JWT implementation without external dependencies
 */

class JWT {
    /**
     * Encode JWT token
     * 
     * @param array $payload Token payload
     * @return string Encoded JWT token
     */
    public static function encode($payload) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];
        
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = self::sign($headerEncoded . '.' . $payloadEncoded);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Decode and verify JWT token
     * 
     * @param string $token JWT token
     * @return array|null Decoded payload or null if invalid
     */
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = self::sign($headerEncoded . '.' . $payloadEncoded);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }
        
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Create signature
     * 
     * @param string $data Data to sign
     * @return string Signature
     */
    private static function sign($data) {
        return hash_hmac('sha256', $data, JWT_SECRET, true);
    }
    
    /**
     * Base64 URL encode
     * 
     * @param string $data Data to encode
     * @return string Encoded string
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     * 
     * @param string $data Data to decode
     * @return string Decoded string
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Generate token payload
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param int $expiry Expiry time in seconds
     * @return array Token payload
     */
    public static function createPayload($userId, $role, $expiry = null) {
        if ($expiry === null) {
            $expiry = JWT_EXPIRY;
        }
        
        return [
            'user_id' => $userId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + $expiry
        ];
    }
}

