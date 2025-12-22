<?php
/**
 * API Authentication Integration Tests
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/bootstrap.php';

class ApiAuthTest extends TestCase
{
    private $baseUrl;
    
    protected function setUp(): void
    {
        $this->baseUrl = APP_URL . '/api/v1';
    }
    
    public function testLoginEndpointExists()
    {
        // Test that login endpoint is accessible
        $ch = curl_init($this->baseUrl . '/auth/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => 'test',
            'password' => 'test'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should return 401 for invalid credentials, but endpoint should exist
        $this->assertContains($httpCode, [200, 401, 400]);
    }
}

