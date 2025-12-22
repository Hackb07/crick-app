# 🏏 CricApp - Comprehensive Technical Specification

**Version:** 2.1.0 (Detailed Architecture & Logic)  
**Date:** 2025-12-09  
**Status:** Active Production & Refactoring  
**Maintainers:** Engineering Team

---

## 1. Executive Summary & Scope
CricApp is a specialized Cricket Management System designed for high-fidelity match scoring and tournament administration. Unlike generic sports apps, it handles the complex state-dependent logic of cricket (overs, bowling limits, extras, dismissals) with a focus on **Data Integrity** and **Real-time Performance**.

**Core Mission:** Convert manual paper scoring into a digital, API-driven process that powers live scoreboards, detailed statistical analysis, and broadcast-ready overlays.

---

## 2. Technology Stack & Rationale

### Backend: PHP 8.2+ (Clean Architecture)
*   **Why PHP?** Universal availability on shared hosting (XAMPP/cPanel), strong typing in 8.x, and ease of deployment.
*   **Framework:** **Custom MVC** (No heavy framework like Laravel).
    *   *Rationale:* Minimal overhead for high-frequency scoring API requests. Full control over the request lifecycle.
*   **Dependency Injection:** Services (e.g., `MatchService`, `ScoreService`) are instantiated as needed to keep memory footprint low.

### Frontend: Vanilla JavaScript (ES6+)
*   **Why No Framework?** To ensure maximum performance on low-end mobile devices used by scorers in the field.
    *   *Performance:* Zero compilation step, instant load times, direct DOM manipulation for critical scoring taps.
*   **State Management:** Custom `StateStore` pattern (in `score-state.js`) mimicking Redux but lightweight.
*   **CSS:** Native CSS Variables & Flexbox. No Bootstrap dependencies for the Scorer App to prevent "CSS Bloat".

### Database: MySQL / MariaDB (Relational)
*   **Engine:** InnoDB (ACID Compliance is critical for transactional scoring).
*   **Optimization:** Heavy indexing on `match_id` and `player_id` for instant stat aggregation.

---

## 3. System Architecture & Logic Flow

### 3.1 The MVC Pattern Implementation
We strictly enforce Separation of Concerns (SoC).

**A. Controller Layer (`admin/matches/*.php`)**
*   **Role:** The "Brain". Handles Authentication, Input Validation, Business Logic execution, and Data Preparation.
*   **Logic Flow:**
    1.  **Auth Check:** `requireRole(['admin', 'scorer'])`.
    2.  **Input:** Validates `$_GET['id']`.
    3.  **Service Call:** `loadScoreData($id)` fetches 50+ data points (Score, Teams, Stats).
    4.  **Data Mapping:** Converts Database `snake_case` to View `camelCase`.
    5.  **Bridge Construction:** Builds the `jsConfig` JSON object (The "Bridge" between PHP & JS).
    6.  **Rendering:** Calls `renderAdminLayout()` with the separated View path.

**B. View Layer (`views/admin/*.php`)**
*   **Role:** The "Face". Pure HTML structure. **Zero instantiation logic.**
*   **Constraint:** No direct SQL queries. No complex PHP loops (only display loops).
*   **Styling:** Uses scoped CSS classes (e.g., `.scorer-card`) defined in page-specific CSS files.

**C. Client Logic Layer (`assets/js/admin/matches/*.js`)**
*   **Role:** The "Hands". Handles interactivity and API communication.
*   **Modules:**
    *   `score-state.js`: Holds the "Single Source of Truth" (Runs, Wickets, Overs).
    *   `score-ui.js`: DOM updates (Update Scoreboard, Toggle Modals).
    *   `score-events.js`: Event Listeners (Click, Swipe).
    *   `score-api.js`: `fetch()` wrappers with Error Handling & Offline Queue.

### 3.2 The "Bridge" Protocol (PHP to JS)
To allow legacy JS modules to function within a modern PHP MVC app, we use a strict Bridge Protocol:
1.  **PHP** calculates the state (`$strikerId`, `$score`).
2.  **PHP** encodes this into a `const MATCH_CONFIG` object inside a `<script>` tag in the Head.
3.  **JS** initializes its state from `MATCH_CONFIG`.
    *   *Benefit:* The page loads with the *correct state immediately*. No "loading spinner" needed to fetch initial score.

---

## 4. Deep-Dive: Core Logic Specifications

### 4.1 Scoring State Machine (`recordRun`)
Cricket scoring is not just "Add 1". It acts as a State Machine.
*   **Logic:**
    1.  **Input:** User presses "1 Run".
    2.  **Validation:** Is match live? Is bowler selected?
    3.  **Local Update:**
        *   Increment `score + 1`.
        *   Increment `striker_runs + 1`.
        *   Increment `bowler_runs + 1`.
        *   Increment `balls + 1`.
        *   **Swap Logic:** If runs is odd (1, 3), swap Striker & Non-Striker IDs.
    4.  **UI Render:** Update DOM immediately (Optimistic UI).
    5.  **API Sync:** Send POST to `api/score/update`.
    6.  **Reconciliation:** If API fails, rollback UI or queue for retry.

### 4.2 Offline Sync Protocol
Critical for fields with poor internet.
*   **Mechanism:** `localStorage` Queue (`score_offline_queue`).
*   **Write Path:** If API returns 500/NetworkError -> Push action object to Queue.
*   **Sync Path:** `setInterval` checks connection every 5s.
    *   If Online && Queue > 0 -> Send Batch Request.
    *   On Success -> Clear Queue -> Update `serverSeq` (Server Sequence ID).
*   **Conflict Resolution:** Server Timestamp wins.

### 4.3 Validation Logic
*   **Bowler Fatigue:** Logic prevents the same bowler from bowling two consecutive overs (`lastOverBowlerId` check).
*   **Dismissals:** "Out" button triggers a specific workflow:
    1.  Select Wicket Type (Bowled, Catch, etc.).
    2.  Select Fielder (if applicable).
    3.  **Critical:** New Batsman must be selected *before* the next ball can be bowled.

---

## 5. Database Schema & Data Integrity

### 5.1 Key Tables
1.  **`matches`**: The Master Record.
    *   `current_innings` (1/2), `state` (live/completed), `team1_id`, `team2_id`.
2.  **`ball_by_ball`**: The Ledger. **Every single event is a row.**
    *   `match_id`, `innings_no`, `over_no`, `ball_no`, `striker_id`, `bowler_id`, `runs`, `extra_type`, `wicket_type`.
    *   *Invariant:* The sum of `runs` + `extras` in this table MUST always equal `matches.score`.
3.  **`player_match_stats`**: Performance Cache.
    *   Stores calculated stats (Runs, Balls, 4s, 6s) to avoid expensive aggregations on every page load.

---

## 6. Security Protocols & Access Control

### 6.1 Authentication Layer
*   **Session Management:** `includes/session.php`.
*   **Login Flow:** POST -> `auth/login.php` -> User verification -> `$_SESSION` hydration.

### 6.2 Role-Based Access Control (RBAC)
*   **Function:** `requireRole($role)`.
*   **Middleware:** Every Controller starts with this check.
    *   `scorer.php`: Requires `['admin', 'scorer']`.
    *   `users.php`: Requires `['admin']`.
*   **Enforcement:** Immediate Header Redirect + `exit` if validation fails.

### 6.3 Input Sanitization (The "Iron Dome")
*   **Protocol:** NEVER trust user input.
*   **XSS Defense:** Output uses `e($variable)` alias for `htmlspecialchars($v, ENT_QUOTES)`.
*   **SQL Injection:** ALL queries use `PDO::prepare()` and `execute()`. Direct variable string concatenation in SQL is **strictly forbidden**.

---

## 7. Developer Standards & Workflow

### 7.1 Naming Conventions
*   **PHP Variables:** `$camelCase` (e.g., `$matchId`, `$currentScore`).
*   **Database Columns:** `snake_case` (e.g., `team_name`, `is_active`).
*   **CSS Classes:** `kebab-case` (e.g., `.scorer-card`, `.player-tab`).
*   **JS Functions:** `camelCase` (e.g., `updateScoreDisplay()`).

### 7.2 Directory & Asset Rules
*   **No "Loose" Files:** All JS must be in `assets/js/...`.
*   **No Inline CSS:** All styling must be in `assets/css/pages/...`.
*   **Strict Imports:** Controllers only import what they need. No generic "include all" calls.

### 7.3 Error Handling Strategy
*   **User Facing:** Show generic error message ("Something went wrong").
*   **Developer Facing:** Log detailed stack trace to `logs/error.log`.
*   **API Errors:** Return JSON `{ success: false, error: "Details" }` with appropriate HTTP Code (400/403/500).

---

## 8. Development Roadmap

### Phase 1: Consolidation (Completed)
*   Standardized `scorer.php` to MVC.
*   Implemented Card Layout UI.
*   Fixed JS dependency paths.

### Phase 2: Intelligence (Active)
*   Add "Wagon Wheel" data entry (Plotting shots).
*   Implement "Predictive Score" algorithms based on current Run Rate.

### Phase 3: Scaling
*   Migrate generic tables to React Components.
*   Implement WebSockets (`Pusher` or `Ratchet`) for millisecond-latency score updates across all devices.

---

*Verified & Approved by Architecture Team.*
