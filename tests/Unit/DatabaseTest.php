<?php
/**
 * Database Class Unit Tests
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/bootstrap.php';

class DatabaseTest extends TestCase
{
    public function testGetInstanceReturnsSingleton()
    {
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }
    
    public function testGetConnectionReturnsPDO()
    {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        $this->assertInstanceOf(PDO::class, $connection);
    }
    
    public function testConnectionCanExecuteQueries()
    {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        $stmt = $connection->query('SELECT 1 as test');
        $result = $stmt->fetch();
        
        $this->assertEquals(1, $result['test']);
    }
}

