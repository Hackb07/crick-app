<?php
/**
 * Performance Tests
 * Tests response times, database query optimization, load handling
 */

use PHPUnit\Framework\TestCase;

class LoadTest extends TestCase {
    private $baseUrl = 'http://localhost/cricapp';
    private $token;
    
    protected function setUp(): void {
        $this->token = $this->login();
    }
    
    private function login() {
        $testUserId = testCreateTestUser('perftest', 'admin');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/v1/auth.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => 'perftest',
            'password' => 'testpass123'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data['data']['token'] ?? null;
    }
    
    private function measureRequest($method, $endpoint, $data = null, $token = null) {
        $start = microtime(true);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
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
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);
        
        $end = microtime(true);
        $elapsed = ($end - $start) * 1000; // Convert to milliseconds
        
        return [
            'code' => $httpCode,
            'time' => $totalTime * 1000, // cURL time in ms
            'elapsed' => $elapsed, // PHP time in ms
            'data' => json_decode($response, true)
        ];
    }
    
    /**
     * Test API response times
     */
    public function testAPIResponseTimes() {
        if (!$this->token) {
            $this->markTestSkipped('Could not authenticate for performance test');
        }
        
        // Test match list endpoint
        $result = $this->measureRequest('GET', '/api/v1/matches.php', null, $this->token);
        
        $this->assertEquals(200, $result['code']);
        // Response should be under 1 second (< 1000ms)
        $this->assertLessThan(1000, $result['time'], 'Match list API response time should be under 1 second');
        
        // Test match detail endpoint (if we have a match)
        $testTeam1Id = testCreateTestTeam('Perf Team 1');
        $testTeam2Id = testCreateTestTeam('Perf Team 2');
        
        $createResult = $this->measureRequest('POST', '/api/v1/matches.php', [
            'team1_id' => $testTeam1Id,
            'team2_id' => $testTeam2Id
        ], $this->token);
        
        if ($createResult['code'] === 200 && isset($createResult['data']['data']['match_id'])) {
            $matchId = $createResult['data']['data']['match_id'];
            
            $detailResult = $this->measureRequest('GET', '/api/v1/matches.php/' . $matchId, null, $this->token);
            $this->assertEquals(200, $detailResult['code']);
            $this->assertLessThan(500, $detailResult['time'], 'Match detail API response time should be under 500ms');
            
            testCleanup('matches', $matchId);
        }
        
        testCleanup('teams', $testTeam1Id);
        testCleanup('teams', $testTeam2Id);
    }
    
    /**
     * Test database query performance
     */
    public function testDatabaseQueryPerformance() {
        $db = Database::getInstance()->getConnection();
        
        // Test match list query
        $start = microtime(true);
        $matchModel = new MatchModel();
        $matches = $matchModel->getAll(['limit' => 100]);
        $end = microtime(true);
        
        $queryTime = ($end - $start) * 1000; // ms
        $this->assertLessThan(500, $queryTime, 'Match list query should complete in under 500ms');
    }
    
    /**
     * Test concurrent request handling
     */
    public function testConcurrentRequests() {
        if (!$this->token) {
            $this->markTestSkipped('Could not authenticate for performance test');
        }
        
        // Simulate concurrent requests (5 simultaneous)
        $multiHandle = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < 5; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/v1/matches.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ]);
            
            $handles[] = $ch;
            curl_multi_add_handle($multiHandle, $ch);
        }
        
        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);
        
        $results = [];
        foreach ($handles as $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            $results[] = ['code' => $httpCode, 'time' => $time];
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($multiHandle);
        
        // Verify all requests succeeded
        foreach ($results as $result) {
            $this->assertEquals(200, $result['code'], 'Concurrent request should succeed');
            $this->assertLessThan(2000, $result['time'] * 1000, 'Concurrent request should complete in under 2 seconds');
        }
    }
    
    /**
     * Test database connection pooling
     */
    public function testDatabaseConnectionReuse() {
        $start = microtime(true);
        
        // Multiple database operations
        $db = Database::getInstance()->getConnection();
        for ($i = 0; $i < 10; $i++) {
            $matchModel = new MatchModel();
            $matches = $matchModel->getAll(['limit' => 10]);
        }
        
        $end = microtime(true);
        $totalTime = ($end - $start) * 1000;
        
        // With connection reuse, this should be fast
        $this->assertLessThan(1000, $totalTime, 'Multiple queries with connection reuse should be fast');
    }
}



