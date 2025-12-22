<?php
/**
 * Integration Tests - API to Database to UI Pipeline
 * Tests that data flows correctly through the system
 */

use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase {
    private $baseUrl = 'http://localhost/cricapp';
    private $token;
    private $testTeam1Id;
    private $testTeam2Id;
    private $testUserId;
    private $testPlayerId;
    
    protected function setUp(): void {
        // Create test data
        $this->testUserId = testCreateTestUser('apitest', 'admin');
        $this->testTeam1Id = testCreateTestTeam('API Team 1');
        $this->testTeam2Id = testCreateTestTeam('API Team 2');
        $this->testPlayerId = testCreateTestPlayer('API Player');
        
        // Login and get token
        $this->token = $this->login();
    }
    
    protected function tearDown(): void {
        // Cleanup will be done after each test
    }
    
    private function login() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/v1/auth.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => 'apitest',
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
            'data' => json_decode($response, true)
        ];
    }
    
    /**
     * Test API → DB → API pipeline
     */
    public function testMatchCreationPipeline() {
        if (!$this->token) {
            $this->markTestSkipped('Could not authenticate for API test');
        }
        
        // Create match via API
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id,
            'venue' => 'Test Venue',
            'overs_per_innings' => 20.0
        ];
        
        $response = $this->apiRequest('POST', '/api/v1/matches.php', $matchData, $this->token);
        
        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['data']['success']);
        
        $matchId = $response['data']['data']['match_id'] ?? null;
        $this->assertNotNull($matchId);
        
        // Retrieve match via API
        $getResponse = $this->apiRequest('GET', '/api/v1/matches.php/' . $matchId, null, $this->token);
        
        $this->assertEquals(200, $getResponse['code']);
        $this->assertNotNull($getResponse['data']['data']);
        $this->assertEquals($matchId, $getResponse['data']['data']['match_id']);
        
        // Verify in database directly
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        $this->assertNotNull($match);
        $this->assertEquals('draft', $match['state']);
        
        // Cleanup
        testCleanup('matches', $matchId);
    }
    
    /**
     * Test admin updates visible on public side
     */
    public function testAdminUpdatesVisiblePublicly() {
        if (!$this->token) {
            $this->markTestSkipped('Could not authenticate for API test');
        }
        
        // Create match
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id
        ];
        
        $response = $this->apiRequest('POST', '/api/v1/matches.php', $matchData, $this->token);
        $matchId = $response['data']['data']['match_id'] ?? null;
        
        if (!$matchId) {
            $this->markTestSkipped('Could not create match for test');
        }
        
        // Admin updates match (public can view, but we test the update)
        $tossData = [
            'toss_winner_id' => $this->testTeam1Id,
            'toss_decision' => 'bat'
        ];
        
        $tossResponse = $this->apiRequest('POST', '/api/v1/matches.php/' . $matchId . '/toss', $tossData, $this->token);
        $this->assertEquals(200, $tossResponse['code']);
        
        // Public can view (no token needed)
        $publicResponse = $this->apiRequest('GET', '/api/v1/matches.php/' . $matchId);
        $this->assertEquals(200, $publicResponse['code']);
        $this->assertEquals('scheduled', $publicResponse['data']['data']['state']);
        
        // Cleanup
        testCleanup('matches', $matchId);
    }
    
    /**
     * Test player addition to match
     */
    public function testPlayerAdditionToMatch() {
        if (!$this->token) {
            $this->markTestSkipped('Could not authenticate for API test');
        }
        
        // Create match
        $matchData = [
            'team1_id' => $this->testTeam1Id,
            'team2_id' => $this->testTeam2Id
        ];
        
        $response = $this->apiRequest('POST', '/api/v1/matches.php', $matchData, $this->token);
        $matchId = $response['data']['data']['match_id'] ?? null;
        
        if (!$matchId) {
            $this->markTestSkipped('Could not create match for test');
        }
        
        // Add player to match
        $playerData = [
            'player_id' => $this->testPlayerId,
            'team_id' => $this->testTeam1Id
        ];
        
        $addResponse = $this->apiRequest('POST', '/api/v1/matches.php/' . $matchId . '/players', $playerData, $this->token);
        $this->assertEquals(200, $addResponse['code']);
        
        // Verify player in match
        $playersResponse = $this->apiRequest('GET', '/api/v1/matches.php/' . $matchId . '/players', null, $this->token);
        $this->assertEquals(200, $playersResponse['code']);
        $this->assertGreaterThan(0, count($playersResponse['data']['data']['teams'] ?? []));
        
        // Cleanup
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM player_appearances WHERE match_id = :match_id");
        $stmt->execute(['match_id' => $matchId]);
        testCleanup('matches', $matchId);
    }
}



