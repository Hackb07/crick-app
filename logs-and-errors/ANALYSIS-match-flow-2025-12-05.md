# Match Administration Flow Analysis

**Date**: 2025-12-05
**Scope**: Admin Panel Match Lifecycle

## 🔄 Complete Workflow

### 1. Match Creation (`create.php`)
- **Input**: Teams (1 & 2), Series, Date, Venue, Overs.
- **Action**: Creates a new match record with state `scheduled`.
- **Next Step**: Redirects to **Match Dashboard** (`view.php`).

### 2. Match Dashboard (`view.php`)
- **Role**: Central hub for a specific match.
- **Displays**: Match details, current status, score (if live/completed).
- **Actions**:
    - **Match Flow** (`flow.php`): The recommended setup wizard.
    - **Edit Details** (`edit.php`): Update venue, date, etc.
    - **Assign Players** (`assign-players.php`): Standalone player assignment.
    - **Score Match** (`score.php`): Only available when state is `live`.

### 3. Setup Wizard (`flow.php`)
This is the primary workflow for preparing a match. It guides the admin through 3 steps:

*   **Step 1: Squads**
    *   **Logic**: Assigns players to Team 1 and Team 2.
    *   **Backend**: Updates `player_appearances` table.
    *   **Validation**: Ensures teams have players before proceeding.

*   **Step 2: Toss**
    *   **Logic**: Records which team won the toss and their decision (bat/bowl).
    *   **Backend**: Updates `toss_winner_id` and `toss_decision` in `matches` table.

*   **Step 3: Start Match**
    *   **Action**: Clicking "Start Match" POSTs to `start.php`.
    *   **Logic**: `start.php` updates match state to `live`.
    *   **Next Step**: Redirects back to `flow.php`, which now shows a "Go to Scoring Console" button.

### 4. Live Scoring (`score.php`)
- **Role**: The interface for recording ball-by-ball data.
- **Data Flow**: Sends events to `api/v1/events.php`.
- **Completion**: When innings/match ends, it finalizes the match via API.
- **Next Step**: Redirects to **Match Dashboard** (`view.php`) which now displays the full scorecard.

## 📂 File Relationships

| File | Purpose | Dependencies |
|------|---------|--------------|
| `create.php` | Create new match | `MatchModel`, `Team` |
| `view.php` | Match dashboard | `MatchModel`, `POTM` |
| `flow.php` | Setup wizard (Squads -> Toss -> Start) | `MatchFlowService`, `MatchStateMachine` |
| `assign-players.php` | Standalone player assignment | `Player`, `Team` |
| `toss.php` | Standalone toss recording | `MatchStateMachine` |
| `start.php` | State transition (Scheduled -> Live) | `MatchStateMachine` |
| `score.php` | Live scoring interface | `ScoreState`, `ScoreEvents` (JS) |

## ✅ Logic Verification
The flow is logically sound and complete.
- **Pre-requisites**: Players must be assigned before Toss. Toss must be recorded before Start.
- **State Management**: `MatchStateMachine` ensures valid transitions (e.g., cannot start without toss).
- **Redundancy**: `assign-players.php` and `toss.php` provide fallback/direct access to specific steps if the wizard (`flow.php`) isn't used.
