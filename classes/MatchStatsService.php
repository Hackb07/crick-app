<?php
/**
 * Match Stats Service
 * 
 * Handles loading match data, players, and calculating statistics from events
 * for the match view page.
 */

class MatchStatsService {
    private $db;
    private $matchModel;
    private $eventModel;
    private $commentaryModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->matchModel = new MatchModel();
        $this->eventModel = new Event();
        $this->commentaryModel = new Commentary();
    }

    /**
     * Get full match statistics and data
     * 
     * @param int $matchId
     * @return array|null Returns null if match not found
     */
    public function getMatchStats($matchId) {
        // 1. Load Match Data
        $match = $this->matchModel->getById($matchId);
        if (!$match) {
            return null;
        }

        // 2. Load Events
        $events = $this->eventModel->getByMatch($matchId) ?? [];

        // 3. Load Commentary
        $commentaryItems = $this->commentaryModel->getByMatch($matchId);

        // 4. Load Players
        $players = [];
        $playerLookup = [];
        
        try {
            // Load all players for this match from player_appearances
            $stmt = $this->db->prepare("
                SELECT p.player_id, p.name, p.photo_url as profile_image,
                       pa.appearance_id, pa.team_id, pa.is_captain,
                       p.batting_style, p.bowling_style
                FROM player_appearances pa 
                JOIN players p ON pa.player_id = p.player_id 
                WHERE pa.match_id = :match_id
            ");
            $stmt->execute(['match_id' => $matchId]);
            $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Index players by ID for quick lookup
            foreach ($players as $p) {
                $playerLookup[$p['player_id']] = $p;
            }
            
            // Fallback: Load all players from database to ensure we can resolve names
            // Only if we have events with player IDs not in appearances (rare but possible)
            $stmt = $this->db->prepare("SELECT player_id, name FROM players");
            $stmt->execute();
            $allPlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allPlayers as $p) {
                if (!isset($playerLookup[$p['player_id']])) {
                    $playerLookup[$p['player_id']] = $p;
                }
            }
            
        } catch (Exception $e) {
            error_log("Error loading players: " . $e->getMessage());
        }

        // Build team players array
        $teamPlayers = [$match['team1_id'] => [], $match['team2_id'] => []];
        foreach ($players as $p) {
            if (isset($teamPlayers[$p['team_id'] ?? 0])) {
                $teamPlayers[$p['team_id']][] = $p;
            }
        }

        // 5. Process Events to Calculate Stats
        $stats = $this->calculateStats($match, $events, $playerLookup);

        return [
            'match' => $match,
            'events' => $events,
            'commentaryItems' => $commentaryItems,
            'players' => $players,
            'playerLookup' => $playerLookup,
            'teamPlayers' => $teamPlayers,
            'inningsData' => $stats['inningsData'],
            'highlights' => $stats['highlights']
        ];
    }

    /**
     * Calculate stats from events
     */
    private function calculateStats($match, $events, $playerLookup) {
        // Initialize Stats Containers
        $inningsData = [
            1 => $this->initInningsData($match, 1),
            2 => $this->initInningsData($match, 2)
        ];

        $currentInnings = 1;
        $currentOver = 1;
        $ballsInOver = 0;
        $overRuns = 0;
        $overWickets = 0;
        $overBalls = [];

        foreach ($events as $event) {
            $payload = json_decode($event['payload_json'] ?? '{}', true);
            $type = $payload['type'] ?? '';
            
            // Determine Innings
            $evtInnings = (int)($payload['innings'] ?? $currentInnings);
            if ($evtInnings > 2) $evtInnings = 2;
            
            // Handle Innings Change
            if ($evtInnings != $currentInnings) {
                // Save previous over if incomplete
                if (!empty($overBalls)) {
                    $inningsData[$currentInnings]['overs'][] = [
                        'over' => count($inningsData[$currentInnings]['overs']) + 1,
                        'runs' => $overRuns,
                        'wickets' => $overWickets,
                        'balls' => $overBalls
                    ];
                }
                
                $currentInnings = $evtInnings;
                $currentOver = 1;
                $ballsInOver = 0;
                $overRuns = 0;
                $overWickets = 0;
                $overBalls = [];
            }
            
            $data = &$inningsData[$currentInnings];
            
            // Extract player IDs
            $strikerId = (int)($payload['striker_id'] ?? 0);
            $bowlerId = (int)($payload['bowler_id'] ?? 0);
            
            // Get names using helper
            $strikerName = $this->getPlayerName($strikerId, $playerLookup);
            $bowlerName = $this->getPlayerName($bowlerId, $playerLookup);
            
            // Update Stats based on event type
            if ($type === 'run') {
                $runs = (int)($payload['runs'] ?? 0);
                $data['total_runs'] += $runs;
                $overRuns += $runs;
                
                // Update batsman stats
                if ($strikerId) {
                    if (!isset($data['batting'][$strikerId])) {
                        $data['batting'][$strikerId] = $this->initBattingStats();
                    }
                    $data['batting'][$strikerId]['runs'] += $runs;
                    $data['batting'][$strikerId]['balls']++;
                    if ($runs == 4) $data['batting'][$strikerId]['4s']++;
                    if ($runs == 6) $data['batting'][$strikerId]['6s']++;
                    
                    $overBalls[] = $runs;
                }
                
                // Update bowler stats
                if ($bowlerId) {
                    if (!isset($data['bowling'][$bowlerId])) {
                        $data['bowling'][$bowlerId] = $this->initBowlingStats();
                    }
                    $data['bowling'][$bowlerId]['runs'] += $runs;
                    $data['bowling'][$bowlerId]['balls']++;
                }
                
                $data['balls_legal']++;
                $ballsInOver++;
                
            } elseif ($type === 'extra') {
                $runs = (int)($payload['runs'] ?? 1);
                $subType = $payload['extra_type'] ?? 'wide';
                
                // Initialize extra type if not exists
                if (!isset($data['extras'][$subType])) {
                    $data['extras'][$subType] = 0;
                }
                
                $data['extras'][$subType] += $runs;
                $data['extras']['total'] += $runs;
                $data['total_runs'] += $runs;
                $overRuns += $runs;
                
                // Check if legal ball
                // Assuming isLegalBall is global or we implement it here. 
                // For safety, implementing logic here:
                $isLegal = !in_array($subType, ['wide', 'no_ball']);
                
                if ($isLegal) {
                    $data['balls_legal']++;
                    $ballsInOver++;
                    $overBalls[] = ($subType === 'bye' ? 'B' : 'LB') . $runs;
                } else {
                    $overBalls[] = ($subType === 'wide' ? 'wd' : 'nb') . ($runs > 1 ? $runs : '');
                    
                    // Update bowler stats for wides/no-balls
                    if ($bowlerId && isset($data['bowling'][$bowlerId])) {
                        if ($subType === 'wide') {
                            $data['bowling'][$bowlerId]['wides']++;
                        } elseif ($subType === 'no_ball') {
                            $data['bowling'][$bowlerId]['nbs']++;
                        }
                    }
                }
                
            } elseif ($type === 'wicket') {
                $data['total_wickets']++;
                $overWickets++;
                $data['balls_legal']++;
                $ballsInOver++;
                
                $overBalls[] = 'W';
                
                // Update batsman stats
                if ($strikerId) {
                    if (!isset($data['batting'][$strikerId])) {
                        $data['batting'][$strikerId] = $this->initBattingStats();
                    }
                    $data['batting'][$strikerId]['balls']++;
                    $data['batting'][$strikerId]['out'] = true;
                    $data['batting'][$strikerId]['dismissal'] = $payload['wicket_type'] ?? 'out';
                    $data['batting'][$strikerId]['bowler'] = $bowlerName;
                }
                
                // Update bowler stats
                if ($bowlerId) {
                    if (!isset($data['bowling'][$bowlerId])) {
                        $data['bowling'][$bowlerId] = $this->initBowlingStats();
                    }
                    $data['bowling'][$bowlerId]['wickets']++;
                    $data['bowling'][$bowlerId]['balls']++;
                }
                
                // Record Fall of Wicket
                $data['fow'][] = [
                    'score' => $data['total_runs'],
                    'wickets' => $data['total_wickets'],
                    'batsman' => $strikerName,
                    'over' => floor($data['balls_legal'] / 6) . '.' . ($data['balls_legal'] % 6)
                ];
            }
            
            // Check if over is complete
            if ($ballsInOver >= 6) {
                $inningsData[$currentInnings]['overs'][] = [
                    'over' => count($inningsData[$currentInnings]['overs']) + 1,
                    'runs' => $overRuns,
                    'wickets' => $overWickets,
                    'balls' => $overBalls
                ];
                
                $ballsInOver = 0;
                $overRuns = 0;
                $overWickets = 0;
                $overBalls = [];
                $currentOver++;
            }
        }

        // Save final incomplete over if exists
        if (!empty($overBalls)) {
            $inningsData[$currentInnings]['overs'][] = [
                'over' => count($inningsData[$currentInnings]['overs']) + 1,
                'runs' => $overRuns,
                'wickets' => $overWickets,
                'balls' => $overBalls
            ];
        }

        // Build Highlights
        $highlights = $this->buildHighlights($events, $playerLookup);

        return [
            'inningsData' => $inningsData,
            'highlights' => $highlights
        ];
    }

    /**
     * Helper to resolve player name with fallback
     */
    private function getPlayerName($id, &$playerLookup) {
        $id = (int)$id;
        if (isset($playerLookup[$id])) {
            return $playerLookup[$id]['name'];
        }
        
        try {
            $stmt = $this->db->prepare("SELECT name FROM players WHERE player_id = ?");
            $stmt->execute([$id]);
            $name = $stmt->fetchColumn();
            if ($name) {
                $playerLookup[$id] = ['name' => $name];
                return $name;
            }
        } catch (Exception $e) {
            // Ignore error
        }
        return 'Unknown';
    }

    private function buildHighlights($events, $playerLookup) {
        $highlights = [];
        $ballCount = 0;
        
        foreach ($events as $event) {
            $payload = json_decode($event['payload_json'] ?? '{}', true);
            $type = $payload['type'] ?? '';
            
            // Calculate over.ball from ball count
            $over = floor($ballCount / 6) + 1;
            $ball = ($ballCount % 6) + 1;
            
            $strikerId = (int)($payload['striker_id'] ?? 0);
            $strikerName = $this->getPlayerName($strikerId, $playerLookup);
            
            if ($type === 'run' && isset($payload['runs']) && ($payload['runs'] == 4 || $payload['runs'] == 6)) {
                $highlights[] = [
                    'type' => $payload['runs'] == 4 ? 'four' : 'six',
                    'player' => $strikerName,
                    'over' => $over,
                    'ball' => $ball
                ];
                $ballCount++; 
            } elseif ($type === 'wicket') {
                $highlights[] = [
                    'type' => 'wicket',
                    'player' => $strikerName,
                    'wicket_type' => $payload['wicket_type'] ?? 'out',
                    'over' => $over,
                    'ball' => $ball
                ];
                $ballCount++; 
            } elseif ($type === 'run') {
                $ballCount++; 
            } elseif ($type === 'extra') {
                $extraType = $payload['extra_type'] ?? 'wide';
                if (!in_array($extraType, ['wide', 'no_ball'])) {
                    $ballCount++; 
                }
            }
        }
        return $highlights;
    }

    private function initInningsData($match, $innings) {
        return [
            'batting' => [],
            'bowling' => [],
            'extras' => ['wide' => 0, 'no_ball' => 0, 'bye' => 0, 'leg_bye' => 0, 'penalty' => 0, 'total' => 0],
            'fow' => [],
            'total_runs' => 0,
            'total_wickets' => 0,
            'balls_legal' => 0,
            'overs' => [],
            'batting_team_id' => getBattingTeam($match, $innings),
            'bowling_team_id' => getBattingTeam($match, $innings) == $match['team1_id'] ? $match['team2_id'] : $match['team1_id']
        ];
    }

    private function initBattingStats() {
        return ['runs' => 0, 'balls' => 0, '4s' => 0, '6s' => 0, 'out' => false, 'dismissal' => '', 'bowler' => ''];
    }

    private function initBowlingStats() {
        return ['overs' => 0, 'maidens' => 0, 'runs' => 0, 'wickets' => 0, 'balls' => 0, 'wides' => 0, 'nbs' => 0];
    }
    /**
     * Get live match data including partnership and recent balls
     */
    public function getLiveMatchData($matchId) {
        $data = $this->getMatchStats($matchId);
        if (!$data) return null;

        $events = $data['events'];
        $match = $data['match'];
        
        // 1. Calculate Partnership
        $partnership = ['runs' => 0, 'balls' => 0];
        $lastWicketSeq = 0;
        
        // Find last wicket sequence
        foreach ($events as $event) {
            $payload = json_decode($event['payload_json'] ?? '{}', true);
            if (($payload['type'] ?? '') === 'wicket') {
                $lastWicketSeq = $event['assigned_server_seq'];
            }
        }
        
        // Sum runs after last wicket
        foreach ($events as $event) {
            if ($event['assigned_server_seq'] > $lastWicketSeq) {
                $payload = json_decode($event['payload_json'] ?? '{}', true);
                $type = $payload['type'] ?? '';
                
                if ($type === 'run') {
                    $partnership['runs'] += (int)($payload['runs'] ?? 0);
                    $partnership['balls']++;
                } elseif ($type === 'extra') {
                    $partnership['runs'] += (int)($payload['runs'] ?? 0);
                    if (!in_array($payload['extra_type'] ?? '', ['wide', 'no_ball'])) {
                        $partnership['balls']++;
                    }
                }
            }
        }
        
        // 2. Recent Balls
        $recentBalls = [];
        $recentEvents = array_slice(array_reverse($events), 0, 12);
        foreach ($recentEvents as $event) {
            $payload = json_decode($event['payload_json'] ?? '{}', true);
            $display = $this->mapEventToBallDisplay($payload);
            if ($display !== null) {
                $recentBalls[] = $display;
            }
            if (count($recentBalls) >= 6) break;
        }
        
        $data['partnership'] = $partnership;
        $data['recentBalls'] = $recentBalls;
        
        return $data;
    }

    private function mapEventToBallDisplay($payload) {
        $type = $payload['type'] ?? '';
        if ($type === 'run') {
            return $payload['runs'];
        } elseif ($type === 'wicket') {
            return 'W';
        } elseif ($type === 'extra') {
            $eType = $payload['extra_type'] ?? '';
            $runs = $payload['runs'] ?? 1;
            if ($eType === 'wide') return 'wd';
            if ($eType === 'no_ball') return 'nb';
            if ($eType === 'bye') return 'B' . $runs;
            if ($eType === 'leg_bye') return 'LB' . $runs;
        }
        return null;
    }
}
