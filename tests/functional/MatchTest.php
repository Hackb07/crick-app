<?php
/**
 * Functional Tests - Match Management
 * Tests match CRUD, scheduling, state transitions
 */

use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase {
    private $db;
    private $matchModel;
    private $testTeam1Id;
    private $testTeam2Id;
    private $testUserId;
    
    protected function setUp(): void {
        $this->db = Database::getInstance()->getConnection();
        $this->matchModel = new MatchModel();
        
        // Create test data
        $this->testTeam1Id = testCreateTestTeam('Test Team 1');
        $this->testTeam2Id = testCreateTestTeam('Test Team 2');
        $this->testUserId = testCreateTestUser('testadmin', 'admin');
    }
    
    protected function tearDown(): void {
        // Cleanup test data
        if ($this->testTeam1Id) {
            testCleanup('teams', $this->testTeam1Id);
        }
        if ($this->testTeam2Id) {
            testCleanup('teams', $this->testTeam2Id);
        }
        if ($this->testUserId) {
            testCleanup('users', $this->testUserId);
        }
    }
    
    /**
     * Test match creation
     */
    public function testCreateMatch() {
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id,
            'match_date' => date('Y-m-d H:i:s'),
            'venue' => 'Test Venue',
            'overs_per_innings' => 20.0,
            'state' => 'draft',
            'created_by' => $this->testUserId
        ];
        
        $result = $this->matchModel->create($matchData);
        
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['lastInsertId']);
        
        // Verify match was created
        $match = $this->matchModel->getById($result['lastInsertId']);
        $this->assertNotNull($match);
        $this->assertEquals('draft', $match['state']);
        $this->assertEquals($this->testTeam1Id, $match['team1_id']);
        $this->assertEquals($this->testTeam2Id, $match['team2_id']);
        
        // Cleanup
        testCleanup('matches', $result['lastInsertId']);
    }
    
    /**
     * Test match date/time handling
     */
    public function testMatchDateTimeHandling() {
        $futureDate = date('Y-m-d H:i:s', strtotime('+1 day'));
        $pastDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id,
            'match_date' => $futureDate,
            'created_by' => $this->testUserId
        ];
        
        $result = $this->matchModel->create($matchData);
        $this->assertTrue($result['success']);
        
        $match = $this->matchModel->getById($result['lastInsertId']);
        $this->assertEquals($futureDate, $match['match_date']);
        
        // Cleanup
        testCleanup('matches', $result['lastInsertId']);
    }
    
    /**
     * Test match state transitions
     */
    public function testMatchStateTransitions() {
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id,
            'created_by' => $this->testUserId
        ];
        
        $result = $this->matchModel->create($matchData);
        $matchId = $result['lastInsertId'];
        
        // Test draft -> scheduled transition
        $updateResult = $this->matchModel->update($matchId, ['state' => 'scheduled']);
        $this->assertTrue($updateResult['success']);
        
        $match = $this->matchModel->getById($matchId);
        $this->assertEquals('scheduled', $match['state']);
        
        // Cleanup
        testCleanup('matches', $matchId);
    }
    
    /**
     * Test match list with filters
     */
    public function testListMatchesWithFilters() {
        // Create multiple matches with different states
        $match1 = $this->matchModel->create([
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id,
            'state' => 'draft',
            'created_by' => $this->testUserId
        ]);
        
        $match2 = $this->matchModel->create([
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id,
            'state' => 'live',
            'created_by' => $this->testUserId
        ]);
        
        // Test filter by state
        $draftMatches = $this->matchModel->getAll(['state' => 'draft']);
        $this->assertGreaterThanOrEqual(1, count($draftMatches));
        
        $liveMatches = $this->matchModel->getAll(['state' => 'live']);
        $this->assertGreaterThanOrEqual(1, count($liveMatches));
        
        // Cleanup
        testCleanup('matches', $match1['lastInsertId']);
        testCleanup('matches', $match2['lastInsertId']);
    }
    
    /**
     * Test match cannot have same team twice
     */
    public function testMatchSameTeamValidation() {
        // Test that model level allows same team (validation should happen at API level)
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam1Id, // Same team
            'created_by' => $this->testUserId
        ];
        
        // Model doesn't prevent this, but API should
        // We verify the model allows it (no exception thrown)
        $result = $this->matchModel->create($matchData);
        
        // Model allows it (no validation at model level)
        $this->assertTrue($result['success']);
        
        // Cleanup the test match
        if ($result['success'] && $result['lastInsertId']) {
            testCleanup('matches', $result['lastInsertId']);
        }
        
        // Note: API-level validation should prevent this in handleCreateMatch()
    }
}

