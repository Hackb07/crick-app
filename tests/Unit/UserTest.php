<?php
/**
 * User Class Unit Tests
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/bootstrap.php';

class UserTest extends TestCase
{
    private $user;
    
    protected function setUp(): void
    {
        $this->user = new User();
    }
    
    public function testAuthenticateWithValidCredentials()
    {
        // This test requires a test user in database
        // Skip if in testing mode without test data
        if (!defined('TESTING_MODE') || !TESTING_MODE) {
            $this->markTestSkipped('Requires test database setup');
        }
        
        $result = $this->user->authenticate('admin', 'admin123');
        
        $this->assertNotNull($result);
        $this->assertArrayHasKey('user_id', $result);
    }
    
    public function testAuthenticateWithInvalidCredentials()
    {
        $result = $this->user->authenticate('nonexistent', 'wrongpassword');
        
        $this->assertNull($result);
    }
    
    public function testGetByIdReturnsUser()
    {
        // This test requires a test user in database
        if (!defined('TESTING_MODE') || !TESTING_MODE) {
            $this->markTestSkipped('Requires test database setup');
        }
        
        $user = $this->user->getById(1);
        
        $this->assertNotNull($user);
        $this->assertArrayHasKey('username', $user);
    }
}

