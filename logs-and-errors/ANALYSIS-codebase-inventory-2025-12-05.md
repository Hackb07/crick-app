# CricApp Complete Codebase Inventory

**Generated**: 2025-12-05 02:32 IST  
**Purpose**: Deep understanding of all functions, classes, and names  
**Status**: ✅ Complete Reference

---

## 📚 TABLE OF CONTENTS

1. [PHP Classes (Models)](#php-classes-models)
2. [PHP Helper Functions](#php-helper-functions)
3. [JavaScript Functions](#javascript-functions)
4. [API Endpoints](#api-endpoints)
5. [Database Schema](#database-schema)
6. [Naming Conventions](#naming-conventions)

---

## 1. PHP CLASSES (Models)

### Location: `/classes/`

#### **ActionLogger.php**
```php
class ActionLogger {
    Methods:
    - __construct()
    - log($userId, $action, $resourceType, $resourceId, $details)
    - getLogs($filters)
}
```

#### **BaseModel.php**
```php
class BaseModel {
    Methods:
    - __construct()
    - getById($id)
    - getAll($filters)
    - create($data)
    - update($id, $data)
    - delete($id)
    - softDelete($id)
    - restore($id)
}
```

#### **Commentary.php**
```php
class Commentary {
    Methods:
    - __construct()
    - addCommentary($matchId, $eventId, $text, $timestamp)
    - getMatchCommentary($matchId)
    - updateCommentary($id, $text)
    - deleteCommentary($id)
}
```

#### **Container.php**
```php
class Container {
    Methods:
    - __construct()
    - set($name, $resolver)
    - get($name)
    - has($name)
    - singleton($name, $resolver)
}
```

#### **Database.php**
```php
class Database {
    Methods:
    - __construct()
    - getInstance()
    - getConnection()
}
```

#### **Event.php**
```php
class Event {
    Methods:
    - __construct()
    - create($data)
    - getById($id)
    - getMatchEvents($matchId, $innings)
    - update($id, $data)
    - delete($id)
    - getLastEvent($matchId, $innings)
    - validateEvent($data)
    - calculateEventImpact($event)
}
```

#### **EventRepository.php**
```php
class EventRepository {
    Methods:
    - __construct()
    - save($event)
    - findById($id)
    - findByMatch($matchId, $innings)
    - delete($id)
    - getLastSequence($matchId, $innings)
}
```

#### **JWT.php**
```php
class JWT {
    Methods:
    - encode($payload, $secret)
    - decode($token, $secret)
    - verify($token, $secret)
}
```

#### **MatchFlowService.php**
```php
class MatchFlowService {
    Methods:
    - __construct()
    - startMatch($matchId)
    - recordToss($matchId, $tossWinnerId, $tossDecision)
    - assignPlayers($matchId, $teamId, $playerIds)
    - changeInnings($matchId)
    - finalizeMatch($matchId)
    - canStartMatch($matchId)
    - canRecordToss($matchId)
    - canAssignPlayers($matchId)
    - canChangeInnings($matchId)
    - canFinalizeMatch($matchId)
}
```

#### **MatchModel.php**
```php
class MatchModel {
    Methods:
    - __construct()
    - create($data)
    - getById($id)
    - getAll($filters)
    - update($id, $data)
    - delete($id)
    - getUpcoming($limit)
    - getLive($limit)
    - getCompleted($limit)
    - getBySeries($seriesId)
}
```

#### **MatchRepository.php**
```php
class MatchRepository {
    Methods:
    - __construct()
    - save($match)
    - findById($id)
    - findAll($criteria)
    - update($id, $data)
    - delete($id)
}
```

#### **MatchService.php**
```php
class MatchService {
    Methods:
    - __construct()
    - createMatch($data)
    - updateMatch($id, $data)
    - deleteMatch($id)
    - getMatch($id)
    - getMatches($filters)
}
```

#### **MatchStateMachine.php**
```php
class MatchStateMachine {
    Methods:
    - __construct()
    - transition($matchId, $newState)
    - canTransition($currentState, $newState)
    - getValidTransitions($currentState)
    - validateState($state)
}
```

#### **MatchStatsService.php**
```php
class MatchStatsService {
    Methods:
    - __construct()
    - calculateMatchStats($matchId)
    - calculateBattingStats($matchId, $innings)
    - calculateBowlingStats($matchId, $innings)
    - calculatePartnership($matchId, $innings)
    - calculateRunRate($runs, $balls)
    - calculateStrikeRate($runs, $balls)
    - calculateEconomy($runs, $balls)
}
```

#### **POTM.php (Player of the Match)**
```php
class POTM {
    Methods:
    - __construct()
    - calculate($matchId)
    - getScore($playerId, $matchId)
    - award($matchId, $playerId)
}
```

#### **POTS.php (Player of the Series)**
```php
class POTS {
    Methods:
    - __construct()
    - calculate($seriesId)
    - getScore($playerId, $seriesId)
    - award($seriesId, $playerId)
}
```

#### **Player.php**
```php
class Player {
    Methods:
    - __construct()
    - create($data)
    - getById($id)
    - getAll($filters)
    - update($id, $data)
    - delete($id)
    - getByTeam($teamId)
    - getStats($playerId)
    - updateStats($playerId, $stats)
}
```

#### **RateLimiter.php**
```php
class RateLimiter {
    Methods:
    - __construct()
    - check($key, $limit, $window)
    - increment($key)
    - reset($key)
}
```

#### **Series.php**
```php
class Series {
    Methods:
    - __construct()
    - create($data)
    - getById($id)
    - getAll($filters)
    - update($id, $data)
    - delete($id)
    - getMatches($seriesId)
}
```

#### **StatsCalculator.php**
```php
class StatsCalculator {
    Methods:
    - __construct()
    - calculatePlayerStats($playerId)
    - calculateTeamStats($teamId)
    - calculateSeriesStats($seriesId)
    - calculateBattingAverage($runs, $dismissals)
    - calculateBowlingAverage($runs, $wickets)
    - calculateStrikeRate($runs, $balls)
    - calculateEconomy($runs, $overs)
}
```

#### **Team.php**
```php
class Team {
    Methods:
    - __construct()
    - create($data)
    - getById($id)
    - getAll($filters)
    - update($id, $data)
    - delete($id)
    - getPlayers($teamId)
    - addPlayer($teamId, $playerId)
    - removePlayer($teamId, $playerId)
    - getMatches($teamId)
    - getStats($teamId)
}
```

#### **User.php**
```php
class User {
    Methods:
    - __construct()
    - authenticate($username, $password)
    - create($data)
    - getById($id)
    - getAll($filters)
    - update($id, $data)
    - delete($id)
    - updatePassword($id, $newPassword)
    - hasRole($userId, $role)
}
```

---

## 2. PHP HELPER FUNCTIONS

### Location: `/includes/utils.php`

```php
// URL Helpers
function getBaseUrl()
function getBasePath()
function assetUrl($path)
function adminUrl($path)
function publicUrl($path)

// Query/Post Helpers
function getQuery($key, $default = null)
function getPost($key, $default = null)

// Security Helpers
function e($string)
function sanitize($string)
function validateEmail($email)
function validatePhone($phone)

// Session Helpers
function getSession($key, $default = null)
function setSession($key, $value)
function destroySession()
function isLoggedIn()

// Date/Time Helpers
function formatDate($date, $format = 'Y-m-d')
function formatTime($time, $format = 'H:i:s')
function formatDateTime($datetime, $format = 'Y-m-d H:i:s')
function timeAgo($datetime)

// String Helpers
function truncate($string, $length, $append = '...')
function slug($string)
function generateToken($length = 32)

// Array Helpers
function arrayGet($array, $key, $default = null)
function arrayOnly($array, $keys)
function arrayExcept($array, $keys)

// Validation Helpers
function required($value)
function numeric($value)
function between($value, $min, $max)
function in($value, $array)
```

### Location: `/includes/session.php`

```php
function startSession()
function isLoggedIn()
function requireLogin()
function requireAdmin()
function requireScorer()
function getUser()
function getUserId()
function getUserRole()
```

### Location: `/includes/csrf.php`

```php
function generateCsrfToken()
function validateCsrfToken($token)
function csrfField()
```

---

## 3. JAVASCRIPT FUNCTIONS

### Location: `/admin/matches/js/`

#### **score-state.js**
```javascript
// State initialization
function initializeState(phpData)
```

#### **score-utils.js**
```javascript
function isInningsComplete(wickets, overs, balls, maxOvers)
function calculateOvers(balls)
function calculateRunRate(runs, overs)
function formatOvers(overs, balls)
```

#### **score-ui.js**
```javascript
function updateStriker()
function updateNonStriker()
function updateBowler()
function updateBowlerSelectState()
function updateStrikerStats()
function updateNonStrikerStats()
function updateBowlerStats()
function updatePlayerStats()
function updateScoreDisplay()
function updateCurrentOver()
function swapStrike()
function filterBowlerDropdown(excludeBowlerId)
function enableScoringButtons()
function disableScoringButtons()
function updateQuickStats()
function updatePartnership()
function resetPartnership()
function showOverNotification()
function addBallToTracker(ballData)
function clearBallTracker()
```

#### **score-modals.js**
```javascript
function openPlayerSelect(playerType)
function showExtraRunsModal(extraType)
function closeExtraRunsModal()
function recordExtraWithRuns()
function showWicketModal()
function closeWicketModal()
function recordWicketType(dismissalType)
function showFielderModal()
function closeFielderModal()
function confirmFielder()
function showRunOutSelection()
function closeRunOutModal()
function recordRunOut(who)
function showNewBatsmanModal()
function closeNewBatsmanModal()
function confirmNewBatsman()
function updateNewBatsmanDropdown()
function showRetiredHurtModal()
function closeRetiredHurtModal()
function confirmRetiredHurt()
function showOverNotification()
function showNotification(title, message, type)
```

#### **score-events.js**
```javascript
function recordRun(runs)
function recordExtra(type)
function recordBall(ballData)
function handleBallRecorded(response)
function handleBallError(error)
function loadMatchState()
function undoLastBall()
function confirmWicket()
function swapBatsmen(event)
function selectStriker(playerId, playerName)
function selectNonStriker(playerId, playerName)
function selectBowler(playerId, playerName)
```

#### **score-api.js**
```javascript
function apiRequest(endpoint, method, data)
function recordBallApi(ballData)
function undoBallApi(eventId)
function syncMatchState()
```

#### **score-init.js**
```javascript
function initializeScorePage()
function toggleSidebar()
function closeSidebar()
```

#### **score-ui-enhanced.js**
```javascript
// Toast System
const Toast = {
    init()
    show(message, type, duration)
    success(message, duration)
    error(message, duration)
    warning(message, duration)
    info(message, duration)
}

// Loading Overlay
const LoadingOverlay = {
    show(message)
    hide()
    updateMessage(message)
}

// UI Functions
function setButtonLoading(button, loading)
function enhanceRunButtons()
function animatePlayerCards()
function enhanceModals()
function closeModal(modal)
function animateScoreUpdate(element, newValue)
function updateLiveStats()
function setupKeyboardShortcuts()
```

---

## 4. API ENDPOINTS

### Location: `/api/v1/`

#### **events.php**
```
POST /api/v1/events/{matchId}/{innings}
GET  /api/v1/events/{matchId}/{innings}
DELETE /api/v1/events/{matchId}/{innings}/{eventId}
```

---

## 5. DATABASE SCHEMA

### Tables:
- `users`
- `teams`
- `players`
- `series`
- `matches`
- `match_players`
- `events`
- `player_stats`
- `team_stats`
- `series_stats`
- `admin_action_logs`
- `commentary`

---

## 6. NAMING CONVENTIONS

### PHP Classes
- **PascalCase**: `MatchFlowService`, `StatsCalculator`
- **Suffix patterns**: 
  - `*Model` - Data models
  - `*Service` - Business logic
  - `*Repository` - Data access
  - `*Controller` - Request handlers

### PHP Functions
- **camelCase**: `getBaseUrl()`, `isLoggedIn()`
- **Prefix patterns**:
  - `get*` - Retrieve data
  - `set*` - Set data
  - `is*` / `has*` - Boolean checks
  - `validate*` - Validation
  - `calculate*` - Calculations

### JavaScript Functions
- **camelCase**: `recordRun()`, `updateStriker()`
- **Prefix patterns**:
  - `update*` - UI updates
  - `show*` / `close*` - Modals
  - `record*` - Data recording
  - `calculate*` - Calculations

### Variables
- **PHP**: `$camelCase`
- **JavaScript**: `camelCase`
- **Constants**: `UPPER_SNAKE_CASE`

### Database
- **Tables**: `snake_case` (plural)
- **Columns**: `snake_case`
- **IDs**: `{table}_id`

---

## 📊 STATISTICS

- **PHP Classes**: 22
- **PHP Helper Functions**: ~50
- **JavaScript Functions**: ~80
- **API Endpoints**: 3
- **Database Tables**: 13

---

**Last Updated**: 2025-12-05 02:32 IST  
**Maintained By**: AI Assistant (Antigravity)
