<?php
/**
 * Match Console Controller
 * Handles logic for the match administration console.
 * 
 * Compliance: @arch:separation, @core:clean
 */

require_once __DIR__ . '/../MatchAdminService.php';

class MatchConsoleController {
    private $service;
    private $matchId;

    public function __construct($matchId) {
        $this->matchId = (int)$matchId;
        $this->service = new MatchAdminService();
    }

    /**
     * Handle incoming POST requests
     */
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $action = getPost('action');
        $result = ['success' => false, 'error' => 'Invalid action'];

        try {
            switch ($action) {
                case 'update_basics':
                    $result = $this->handleUpdateBasics();
                    break;
                case 'update_squad':
                    $result = $this->handleUpdateSquad();
                    break;
                case 'record_toss':
                    $result = $this->handleRecordToss();
                    break;
                case 'start_match':
                    $result = $this->handleStartMatch();
                    break;
            }
        } catch (Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * Get data required for the view
     */
    public function getViewData() {
        $data = $this->service->getConsoleData($this->matchId);
        
        if (isset($data['error'])) {
            throw new Exception($data['error']);
        }

        return $data;
    }

    private function handleUpdateBasics() {
        $match = $this->service->getMatch($this->matchId);
        
        // Handle series_id: convert empty string to NULL
        $seriesId = getPost('series_id');
        if ($seriesId === '' || $seriesId === '0') {
            $seriesId = null;
        }
        
        $updateData = [
            'series_id' => $seriesId,
            'match_date' => getPost('match_date'),
            'venue' => getPost('venue'),
            'overs_per_innings' => getPost('overs_per_innings'),
            'team1_id' => $match['team1_id'],
            'team2_id' => $match['team2_id']
        ];
        return $this->service->updateBasics($this->matchId, $updateData);
    }

    private function handleUpdateSquad() {
        $teamId = (int)getPost('team_id');
        $playerIds = getPost('player_ids', []);
        $meta = [
            'guests' => [],
            'captains' => []
        ];

        foreach ($_POST as $key => $val) {
            if (strpos($key, 'is_guest_') === 0) {
                $pid = (int)substr($key, 9);
                $meta['guests'][$pid] = 1;
            }
            if (strpos($key, 'is_captain_') === 0) {
                $pid = (int)substr($key, 11);
                $meta['captains'][$pid] = 1;
            }
            if (strpos($key, 'is_wk_') === 0) {
                $pid = (int)substr($key, 6);
                $meta['wks'][$pid] = 1;
            }
        }
        
        return $this->service->setSquad($this->matchId, $teamId, $playerIds, $meta);
    }

    private function handleRecordToss() {
        $winnerId = (int)getPost('toss_winner_id');
        $decision = getPost('toss_decision');
        return $this->service->setToss($this->matchId, $winnerId, $decision);
    }

    private function handleStartMatch() {
        return $this->service->startMatch($this->matchId);
    }
}
