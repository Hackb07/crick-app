# Match Admin Console Architecture

**Date**: 2025-12-05
**Status**: ✅ Complete (Phases 1-3 Implemented)

## 🏗️ Overview
The Match Admin Console (`console.php`) replaces the fragmented workflow of `flow.php`, `assign-players.php`, `toss.php`, and `start.php` with a single, unified interface.

## 🧩 Components

### 1. MatchAdminService (`classes/MatchAdminService.php`)
This service encapsulates all logic for match administration.
*   `getConsoleData($matchId)`: Returns all data needed for the UI (Match, Teams, Squads, Validation).
*   `updateBasics($matchId, $data)`: Updates series, date, venue, overs.
*   `setSquad($matchId, $teamId, $playerIds, $meta)`: Handles player assignment, including Guest/Captain flags.
*   `setToss($matchId, $winnerId, $decision)`: Records toss.
*   `startMatch($matchId)`: Transitions state to `live`.

### 2. Match Console (`admin/matches/console.php`)
The UI is divided into 4 tabs with a modern "Glassmorphism" design:
1.  **Basics**: Edit match details.
2.  **Squads**: Select players for both teams side-by-side with search.
3.  **Toss**: Record toss (enabled only when squads are valid).
4.  **Start**: Big button to go live (enabled only when Toss is recorded).

## 🔄 Flow
1.  **Create**: `create.php` -> Redirects to `console.php`.
2.  **Setup**: Admin uses tabs in `console.php` to prepare the match.
3.  **Live**: Clicking "Start" in `console.php` transitions to `live` state and offers a link to `score.php`.
4.  **Scoring**: `score.php` handles the live match.

## 🗑️ Deleted Files
The following files have been removed as part of the refactor:
*   `admin/matches/flow.php`
*   `admin/matches/assign-players.php`
*   `admin/matches/toss.php`
*   `admin/matches/start.php`
*   `admin/matches/edit.php`
