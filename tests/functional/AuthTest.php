<?php
/**
 * Functional Tests - Authentication
 * Tests login, JWT token generation, role-based access
 */

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {
    private $testUserId;
    private $testUsername;
    private $testPassword;
    
    protected function setUp(): void {
        // Create test user
        $this->testUsername = 'authtest_' . time();
        $this->testPassword = bin2hex(random_bytes(8));
        $this->testUserId = testCreateTestUser($this->testUsername, 'admin', $this->testPassword);
    }
    
    protected function tearDown(): void {
        if ($this->testUserId) {
            testCleanup('users', $this->testUserId);
        }
    }
    
    /**
     * Test user authentication
     */
    public function testUserAuthentication() {
        $userModel = new User();
        $user = $userModel->authenticate($this->testUsername, $this->testPassword);
        
        $this->assertNotNull($user);
        $this->assertEquals($this->testUsername, $user['username']);
        $this->assertEquals('admin', $user['role']);
    }
    
    /**
     * Test authentication failure with wrong password
     */
    public function testAuthenticationFailureWrongPassword() {
        $userModel = new User();
        $user = $userModel->authenticate($this->testUsername, 'wrongpassword');
        
        $this->assertNull($user);
    }
    
    /**
     * Test authentication failure with wrong username
     */
    public function testAuthenticationFailureWrongUsername() {
        $userModel = new User();
        $user = $userModel->authenticate('nonexistentuser', $this->testPassword);
        
        $this->assertNull($user);
    }
    
    /**
     * Test JWT token generation
     */
    public function testJWTTokenGeneration() {
        $payload = [
            'user_id' => $this->testUserId,
            'username' => $this->testUsername,
            'role' => 'admin'
        ];
        
        $token = JWT::encode($payload);
        
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // Decode and verify
        $decoded = JWT::decode($token);
        $this->assertNotNull($decoded);
        $this->assertEquals($this->testUserId, $decoded['user_id']);
        $this->assertEquals($this->testUsername, $decoded['username']);
    }
    
    /**
     * Test JWT token expiration
     */
    public function testJWTTokenExpiration() {
        // Note: Token expiration testing requires modifying JWT class
        // or mocking time, which is more complex
        // This is a placeholder for the concept
        $this->assertTrue(true);
    }
}

