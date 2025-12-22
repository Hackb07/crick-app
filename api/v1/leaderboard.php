<?php
/**
 * Leaderboard API
 * 
 * Returns top players for various categories (runs, wickets, etc.)
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $category = getQuery('category', 'runs');
    $limit = (int)getQuery('limit', 10);

    // Batting Leaderboards
    $battingQueries = [
        'runs' => "SELECT p.name, p.photo_url, SUM(bs.runs) as total_runs, COUNT(DISTINCT bs.match_id) as matches,
                          ROUND(SUM(bs.runs) / NULLIF(SUM(bs.balls), 0) * 100, 2) as strike_rate,
                          SUM(bs.fours) as fours, SUM(bs.sixes) as sixes
                   FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                   GROUP BY bs.player_id HAVING total_runs > 0 ORDER BY total_runs DESC LIMIT :limit",
        'sixes' => "SELECT p.name, p.photo_url, SUM(bs.sixes) as total_sixes, SUM(bs.runs) as total_runs
                    FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                    GROUP BY bs.player_id HAVING total_sixes > 0 ORDER BY total_sixes DESC LIMIT :limit",
        'fours' => "SELECT p.name, p.photo_url, SUM(bs.fours) as total_fours, SUM(bs.runs) as total_runs
                    FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                    GROUP BY bs.player_id HAVING total_fours > 0 ORDER BY total_fours DESC LIMIT :limit",
        'thirties' => "SELECT p.name, p.photo_url, COUNT(*) as thirties, SUM(bs.runs) as total_runs
                       FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                       WHERE bs.runs >= 30 AND bs.runs < 50
                       GROUP BY bs.player_id ORDER BY thirties DESC LIMIT :limit",
        'twenties' => "SELECT p.name, p.photo_url, COUNT(*) as twenties, SUM(bs.runs) as total_runs
                       FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                       WHERE bs.runs >= 20 AND bs.runs < 30
                       GROUP BY bs.player_id ORDER BY twenties DESC LIMIT :limit",
        'tens' => "SELECT p.name, p.photo_url, COUNT(*) as tens, SUM(bs.runs) as total_runs
                   FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                   WHERE bs.runs >= 10 AND bs.runs < 20
                   GROUP BY bs.player_id ORDER BY tens DESC LIMIT :limit"
    ];

    // Bowling Leaderboards
    $bowlingQueries = [
        'wickets' => "SELECT p.name, p.photo_url, SUM(bw.wickets) as total_wickets, SUM(bw.runs) as runs_conceded,
                             SUM(bw.balls) as balls_bowled, ROUND(SUM(bw.runs) / NULLIF(SUM(bw.wickets), 0), 2) as average,
                             ROUND(SUM(bw.runs) / (SUM(bw.balls) / 6), 2) as economy
                      FROM bowling_stats bw JOIN players p ON bw.player_id = p.player_id
                      GROUP BY bw.player_id HAVING total_wickets > 0 ORDER BY total_wickets DESC LIMIT :limit",
        'economy' => "SELECT p.name, p.photo_url, SUM(bw.wickets) as total_wickets,
                             ROUND(SUM(bw.runs) / (SUM(bw.balls) / 6), 2) as economy
                      FROM bowling_stats bw JOIN players p ON bw.player_id = p.player_id
                      WHERE bw.balls >= 12 GROUP BY bw.player_id ORDER BY economy ASC LIMIT :limit"
    ];

    // Fielding Leaderboards
    $fieldingQueries = [
        'catches' => "SELECT p.name, p.photo_url, SUM(fs.catches) as total_catches
                      FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                      GROUP BY fs.player_id HAVING total_catches > 0 ORDER BY total_catches DESC LIMIT :limit",
        'runouts' => "SELECT p.name, p.photo_url, SUM(fs.run_outs) as total_runouts
                      FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                      GROUP BY fs.player_id HAVING total_runouts > 0 ORDER BY total_runouts DESC LIMIT :limit",
        'stumpings' => "SELECT p.name, p.photo_url, SUM(fs.stumpings) as total_stumpings
                        FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                        GROUP BY fs.player_id HAVING total_stumpings > 0 ORDER BY total_stumpings DESC LIMIT :limit"
    ];

    $allQueries = array_merge($battingQueries, $bowlingQueries, $fieldingQueries);
    
    // Check if tables exist before running query to avoid 500 errors
    $tablesExist = true;
    try {
        $db->query("SELECT 1 FROM batting_stats LIMIT 1");
    } catch(Exception $e) {
        $tablesExist = false;
    }

    $leaders = [];
    if($tablesExist) {
        if (isset($allQueries[$category])) {
            $sql = $allQueries[$category];
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
             // Default query if category not found (e.g. 'runs')
             $sql = $battingQueries['runs'];
             $stmt = $db->prepare($sql);
             $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
             $stmt->execute();
             $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    echo json_encode(['success' => true, 'data' => $leaders]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
