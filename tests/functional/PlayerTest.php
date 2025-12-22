<?php
/**
 * Functional Tests - Player Management
 * Tests player CRUD operations
 */

use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase {
    private $db;
    private $playerModel;
    
    protected function setUp(): void {
        $this->db = Database::getInstance()->getConnection();
        $this->playerModel = new Player();
    }
    
    /**
     * Test player creation
     */
    public function testCreatePlayer() {
        $playerData = [
            'name' => 'Test Player ' . time(),
            'date_of_birth' => '1990-01-01',
            'batting_hand' => 'right',
            'bowling_style' => 'fast'
        ];
        
        $result = $this->playerModel->create($playerData);
        
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['lastInsertId']);
        
        // Verify player was created
        $player = $this->playerModel->getById($result['lastInsertId']);
        $this->assertNotNull($player);
        $this->assertEquals($playerData['name'], $player['name']);
        $this->assertEquals($playerData['batting_hand'], $player['batting_hand']);
        
        // Cleanup
        testCleanup('players', $result['lastInsertId']);
    }
    
    /**
     * Test player update
     */
    public function testUpdatePlayer() {
        $playerData = [
            'name' => 'Test Player ' . time(),
            'batting_hand' => 'right'
        ];
        
        $result = $this->playerModel->create($playerData);
        $playerId = $result['lastInsertId'];
        
        // Update player
        $updateData = [
            'name' => 'Updated Player Name',
            'batting_hand' => 'left'
        ];
        
        $updateResult = $this->playerModel->update($playerId, $updateData);
        $this->assertTrue($updateResult['success']);
        
        // Verify update
        $player = $this->playerModel->getById($playerId);
        $this->assertEquals($updateData['name'], $player['name']);
        $this->assertEquals($updateData['batting_hand'], $player['batting_hand']);
        
        // Cleanup
        testCleanup('players', $playerId);
    }
    
    /**
     * Test player list with search
     */
    public function testListPlayersWithSearch() {
        $playerData = [
            'name' => 'Unique Search Player ' . time(),
            'batting_hand' => 'right'
        ];
        
        $result = $this->playerModel->create($playerData);
        $playerId = $result['lastInsertId'];
        
        // Search for player
        $players = $this->playerModel->getAll(['search' => 'Unique Search']);
        $this->assertGreaterThanOrEqual(1, count($players));
        
        // Cleanup
        testCleanup('players', $playerId);
    }
    
    /**
     * Test player statistics retrieval
     */
    public function testGetPlayerStats() {
        $playerData = ['name' => 'Stats Player ' . time()];
        $result = $this->playerModel->create($playerData);
        $playerId = $result['lastInsertId'];
        
        // Get stats (should return even if empty)
        $stats = $this->playerModel->getStats($playerId);
        $this->assertNotNull($stats);
        
        // Cleanup
        testCleanup('players', $playerId);
    }
}



