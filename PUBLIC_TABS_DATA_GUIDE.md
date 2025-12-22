# Public Match View - Tabs and Data Guide

## Overview

The public match view (`public/match-view.php`) now has **tabbed navigation** with organized data accessible to all users. Each tab displays different match information.

## Tabs Available

### 1. **Info Tab** (`?tab=info`)
**Shows:**
- Match information card (team names, series, venue, date)
- Toss information (winner and decision)
- Match result banner (if match is completed)

**Data Accessed:**
- `matchData` - Full match details
- `innings1Data` & `innings2Data` - For result calculation
- Teams, series, venue information

**URL:** `/cricapp/public/match-view.php?id={match_id}&tab=info`

### 2. **Live Tab** (links to `live-match.php`)
**Shows:**
- Live scorecard with current batters and bowlers
- Real-time match updates
- Current run rate, partnership, overs left
- Target display (for innings 2)

**Data Accessed:**
- Real-time match events
- Current innings stats
- Live batting/bowling stats

**URL:** `/cricapp/public/live-match.php?id={match_id}`

### 3. **Scorecard Tab** (`?tab=scorecard`) - DEFAULT
**Shows:**
- Full scorecard for both innings
- Batting statistics (runs, balls, 4s, 6s, strike rate)
- Bowling statistics (overs, maidens, runs, wickets, economy)
- Fall of wickets
- Powerplay statistics
- Partnerships
- Recent commentary

**Data Accessed:**
- `innings1Data['batting_stats']` - All batsmen from innings 1
- `innings1Data['bowling_stats']` - All bowlers from innings 1
- `innings2Data['batting_stats']` - All batsmen from innings 2
- `innings2Data['bowling_stats']` - All bowlers from innings 2
- `innings1Data['fall_of_wickets']` - Fall of wickets for innings 1
- `innings2Data['fall_of_wickets']` - Fall of wickets for innings 2
- `innings1Data['partnerships']` - Partnerships for innings 1
- `innings2Data['partnerships']` - Partnerships for innings 2
- `events` - Recent match events for commentary

**URL:** `/cricapp/public/match-view.php?id={match_id}&tab=scorecard` or `/cricapp/public/match-view.php?id={match_id}`

### 4. **Squads Tab** (`?tab=squads`)
**Shows:**
- Team 1 squad with all players
- Team 2 squad with all players
- Player details: Name, Date of Birth, Batting Hand, Bowling Style

**Data Accessed:**
- `team1Squad` - Array of team 1 players (from `player_appearances`)
- `team2Squad` - Array of team 2 players (from `player_appearances`)
- Player information (name, DOB, batting hand, bowling style)

**SQL Query:**
```sql
SELECT pa.*, p.name as player_name, p.player_id, t.name as team_name, t.team_id
FROM player_appearances pa
JOIN players p ON pa.player_id = p.player_id
JOIN teams t ON pa.team_id = t.team_id
WHERE pa.match_id = :match_id
ORDER BY t.team_id, p.name
```

**URL:** `/cricapp/public/match-view.php?id={match_id}&tab=squads`

### 5. **Overs Tab** (`?tab=overs`)
**Shows:**
- Ball-by-ball commentary organized by over
- Each over shows all balls/events in that over
- Commentary text for each event
- Timestamp for each event

**Data Accessed:**
- `ballByBallData` - All events for the match ordered by sequence
- Events organized by over (e.g., "Over 5.3")
- Commentary for each ball

**SQL Query:**
```sql
SELECT e.*, pa.team_id, pa.player_id, p.name as player_name,
       t1.name as team1_name, t2.name as team2_name
FROM events e
LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
LEFT JOIN players p ON pa.player_id = p.player_id
LEFT JOIN matches m ON e.match_id = m.match_id
LEFT JOIN teams t1 ON m.team1_id = t1.team_id
LEFT JOIN teams t2 ON m.team2_id = t2.team_id
WHERE e.match_id = :match_id
ORDER BY e.assigned_server_seq ASC
```

**URL:** `/cricapp/public/match-view.php?id={match_id}&tab=overs`

## Data Accessibility

All tabs are **publicly accessible** - no authentication required. All data is:
- ✅ Read-only (no modifications possible)
- ✅ Safe from SQL injection (uses prepared statements)
- ✅ XSS protected (uses `htmlspecialchars()`)
- ✅ Properly formatted and organized

## Navigation Flow

```
Live Match Page (live-match.php)
    ↓
Tabs: [Info] [Live] [Scorecard] [Squads] [Overs]
    ↓
Match View Page (match-view.php) - Shows selected tab content
```

## Tab Navigation

All tabs link to `match-view.php` with `?tab={tab_name}` parameter:
- **Info**: `match-view.php?id={id}&tab=info`
- **Live**: `live-match.php?id={id}` (separate page)
- **Scorecard**: `match-view.php?id={id}&tab=scorecard`
- **Squads**: `match-view.php?id={id}&tab=squads`
- **Overs**: `match-view.php?id={id}&tab=overs`

## Features

### Active Tab Highlighting
- Active tab is highlighted in blue with underline
- Tab state is maintained via URL parameter

### Responsive Design
- Tabs scroll horizontally on mobile
- Content adapts to screen size
- Tables are responsive

### Data Organization
- **Info**: Basic match information
- **Scorecard**: Complete statistical breakdown
- **Squads**: Team lineups
- **Overs**: Detailed ball-by-ball commentary

## Example URLs

- **Info Tab**: `http://localhost/cricapp/public/match-view.php?id=19&tab=info`
- **Scorecard Tab**: `http://localhost/cricapp/public/match-view.php?id=19&tab=scorecard`
- **Squads Tab**: `http://localhost/cricapp/public/match-view.php?id=19&tab=squads`
- **Overs Tab**: `http://localhost/cricapp/public/match-view.php?id=19&tab=overs`
- **Live Tab**: `http://localhost/cricapp/public/live-match.php?id=19`

## Data Queries Summary

All tabs use efficient queries:
- Single query per data type
- JOINs for related data
- Ordered by sequence/date
- No N+1 query problems

## Notes

- Default tab is **Scorecard** if no tab parameter is specified
- All data is calculated from events table
- Statistics are computed on-the-fly from match events
- No caching implemented (can be added for performance)



