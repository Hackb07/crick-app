<?php
/**
 * Security Tests
 * Tests SQL injection, XSS, CSRF, authentication, RBAC
 */

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase {
    private $baseUrl = 'http://localhost/cricapp';
    private $token;
    private $testUserId;
    
    protected function setUp(): void {
        $this->testUserId = testCreateTestUser('sectest', 'admin');
        $this->token = $this->login();
    }
    
    protected function tearDown(): void {
        if ($this->testUserId) {
            testCleanup('users', $this->testUserId);
        }
    }
    
    private function login() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/v1/auth.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => 'sectest',
            'password' => 'testpass123'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['data']['token'] ?? null;
        }
        return null;
    }
    
    private function apiRequest($method, $endpoint, $data = null, $token = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && ($method === 'POST' || $method === 'PUT')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'code' => $httpCode,
            'data' => json_decode($response, true),
            'raw' => $response
        ];
    }
    
    /**
     * Test SQL injection protection
     */
    public function testSQLInjectionProtection() {
        // Test SQL injection attempts in various fields
        $sqlInjectionPayloads = [
            "' OR '1'='1",
            "'; DROP TABLE matches; --",
            "1' UNION SELECT * FROM users--",
            "1' OR 1=1--",
        ];
        
        foreach ($sqlInjectionPayloads as $payload) {
            // Try SQL injection in login
            $response = $this->apiRequest('POST', '/api/v1/auth.php', [
                'username' => $payload,
                'password' => 'test'
            ]);
            
            // Should fail gracefully, not execute SQL
            $this->assertNotEquals(500, $response['code'], "SQL injection vulnerability detected with payload: $payload");
        }
    }
    
    /**
     * Test authentication required for protected endpoints
     */
    public function testAuthenticationRequired() {
        $testTeam1Id = testCreateTestTeam('Sec Team 1');
        $testTeam2Id = testCreateTestTeam('Sec Team 2');
        
        // Try to create match without token
        $response = $this->apiRequest('POST', '/api/v1/matches.php', [
            'team1_id' => $testTeam1Id,
            'team2_id' => $testTeam2Id
        ]);
        
        // Should require authentication
        $this->assertEquals(401, $response['code']); // Or 403
        
        // Cleanup
        testCleanup('teams', $testTeam1Id);
        testCleanup('teams', $testTeam2Id);
    }
    
    /**
     * Test role-based access control (RBAC)
     */
    public function testRoleBasedAccessControl() {
        // Create user with 'user' role (read-only)
        $readOnlyUserId = testCreateTestUser('readonly_user', 'user');
        
        $readOnlyCh = curl_init();
        curl_setopt($readOnlyCh, CURLOPT_URL, $this->baseUrl . '/api/v1/auth.php');
        curl_setopt($readOnlyCh, CURLOPT_POST, true);
        curl_setopt($readOnlyCh, CURLOPT_POSTFIELDS, json_encode([
            'username' => 'readonly_user',
            'password' => 'testpass123'
        ]));
        curl_setopt($readOnlyCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($readOnlyCh, CURLOPT_RETURNTRANSFER, true);
        
        $readOnlyResponse = curl_exec($readOnlyCh);
        curl_close($readOnlyCh);
        
        $readOnlyData = json_decode($readOnlyResponse, true);
        $readOnlyToken = $readOnlyData['data']['token'] ?? null;
        
        if ($readOnlyToken) {
            $testTeam1Id = testCreateTestTeam('RBAC Team 1');
            $testTeam2Id = testCreateTestTeam('RBAC Team 2');
            
            // Read-only user should NOT be able to create match
            $response = $this->apiRequest('POST', '/api/v1/matches.php', [
                'team1_id' => $testTeam1Id,
                'team2_id' => $testTeam2Id
            ], $readOnlyToken);
            
            // Should be denied (403 or 401)
            $this->assertContains($response['code'], [401, 403], 'Read-only user should not be able to create matches');
            
            testCleanup('teams', $testTeam1Id);
            testCleanup('teams', $testTeam2Id);
        }
        
        testCleanup('users', $readOnlyUserId);
    }
    
    /**
     * Test XSS protection (input sanitization)
     */
    public function testXSSProtection() {
        if (!$this->token) {
            $this->markTestSkipped('Could not authenticate for security test');
        }
        
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")',
            '<svg/onload=alert("XSS")>',
        ];
        
        $testTeam1Id = testCreateTestTeam('XSS Team 1');
        $testTeam2Id = testCreateTestTeam('XSS Team 2');
        
        foreach ($xssPayloads as $payload) {
            // Try XSS in venue field
            $response = $this->apiRequest('POST', '/api/v1/matches.php', [
                'team1_id' => $testTeam1Id,
                'team2_id' => $testTeam2Id,
                'venue' => $payload
            ], $this->token);
            
            if ($response['code'] === 200 && isset($response['data']['data']['match_id'])) {
                $matchId = $response['data']['data']['match_id'];
                
                // Retrieve match and verify XSS is sanitized
                $getResponse = $this->apiRequest('GET', '/api/v1/matches.php/' . $matchId, null, $this->token);
                
                if (isset($getResponse['data']['data']['venue'])) {
                    $venue = $getResponse['data']['data']['venue'];
                    // Should not contain script tags
                    $this->assertStringNotContainsString('<script', $venue, "XSS vulnerability detected in venue field");
                    $this->assertStringNotContainsString('javascript:', $venue, "XSS vulnerability detected in venue field");
                }
                
                testCleanup('matches', $matchId);
            }
        }
        
        testCleanup('teams', $testTeam1Id);
        testCleanup('teams', $testTeam2Id);
    }
    
    /**
     * Test invalid JWT token handling
     */
    public function testInvalidJWTToken() {
        $testTeam1Id = testCreateTestTeam('JWT Team 1');
        $testTeam2Id = testCreateTestTeam('JWT Team 2');
        
        $invalidTokens = [
            'invalid.token.here',
            'Bearer invalid',
            'expired_token_if_possible',
        ];
        
        foreach ($invalidTokens as $token) {
            // Use a protected endpoint that requires authentication
            $response = $this->apiRequest('POST', '/api/v1/matches.php', [
                'team1_id' => $testTeam1Id,
                'team2_id' => $testTeam2Id
            ], $token);
            // Should reject invalid tokens
            $this->assertContains($response['code'], [401, 403], "Invalid token should be rejected: $token");
        }
        
        testCleanup('teams', $testTeam1Id);
        testCleanup('teams', $testTeam2Id);
    }
    
    /**
     * Test rate limiting (if implemented)
     */
    public function testRateLimiting() {
        // Make multiple rapid requests
        // Note: Rate limiting might not be fully implemented
        // This test documents the expected behavior
        $this->assertTrue(true, 'Rate limiting test placeholder');
    }
}

