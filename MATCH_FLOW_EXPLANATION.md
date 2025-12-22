# Cricket Match Flow - Two Innings System

## Overview
This document explains how a cricket match works with **2 innings** - each team bats once and bowls once.

---

## Match Flow Step-by-Step

### **STEP 1: Match Setup & Toss**
- Admin creates a match between **Team A** and **Team B**
- Admin records the **toss**:
  - Which team won the toss
  - What they chose: **Bat** or **Bowl**

---

### **STEP 2: INNINGS 1 - Team A Bats, Team B Bowls**

#### What Happens:
- **Team A** bats first (if they won toss and chose to bat)
- **Team B** bowls first

#### Scoring:
- Score runs for Team A batters
- Record wickets when Team A batters get out
- Track overs bowled (e.g., 20 overs per innings)

#### When Innings 1 Ends:
- **Automatic**: When overs limit is reached (e.g., 20 overs)
- **Manual**: Click **"Complete Innings"** button when innings finishes

#### Example Result:
```
Innings 1 Complete:
Team A: 150 runs / 8 wickets in 20 overs
```

---

### **STEP 3: TRANSITION - Switch Teams**

#### What Happens Automatically:
1. System detects Innings 1 is complete
2. Shows **"Complete Innings"** button
3. When clicked:
   - System sets `current_innings = 2`
   - Teams **automatically swap**:
     - **Team A** now bowls
     - **Team B** now bats
4. Score resets to **0-0** for Innings 2
5. Page refreshes to show new team selections

#### Important:
- The system remembers all Innings 1 scores
- You start fresh scoring for Innings 2
- Both innings scores are tracked separately

---

### **STEP 4: INNINGS 2 - Team B Bats, Team A Bowls**

#### What Happens:
- **Team B** bats second
- **Team A** bowls second

#### Scoring:
- Score runs for Team B batters
- Record wickets when Team B batters get out
- Track overs bowled

#### Example Result:
```
Innings 2 Complete:
Team B: 145 runs / 7 wickets in 20 overs
```

---

### **STEP 5: MATCH COMPLETION - Determine Winner**

#### When Innings 2 Ends:
- **Automatic**: When overs limit is reached
- **Manual**: Click **"Finish Match"** button

#### System Automatically Calculates:
1. **Innings 1 Total**: Team A score
2. **Innings 2 Total**: Team B score
3. **Compare Scores**:
   - If **Team A** (150) > **Team B** (145):
     - **Winner**: Team A
     - **Result**: "Team A won by 5 runs"
   - If **Team B** (160) > **Team A** (150):
     - **Winner**: Team B
     - **Result**: "Team B won by 10 runs"
   - If scores are equal:
     - **Result**: "Match Tied"

#### Match State Changes:
- Match state: `live` → `completed`
- Final scorecard shows both innings
- Winner is displayed prominently

---

## Visual Flow Diagram

```
┌─────────────────────────────────────────────────┐
│   MATCH SETUP                                    │
│   - Create Match (Team A vs Team B)             │
│   - Record Toss (Team A won, chose to BAT)      │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│   INNINGS 1                                      │
│   ┌─────────────────────────────────────────┐   │
│   │ Team A: BATTING                        │   │
│   │ Team B: BOWLING                        │   │
│   │                                         │   │
│   │ Score: 0-0                              │   │
│   │ → Score runs for Team A                │   │
│   │ → Record wickets                       │   │
│   │ → Track overs (1...20)                │   │
│   └─────────────────────────────────────────┘   │
│                                                 │
│   Result: Team A - 150/8 in 20 overs          │
│   [Click "Complete Innings"]                    │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
        ┌─────────────────────┐
        │  TRANSITION         │
        │  - Teams Swap       │
        │  - current_innings  │
        │    = 2              │
        └─────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│   INNINGS 2                                      │
│   ┌─────────────────────────────────────────┐   │
│   │ Team B: BATTING                        │   │
│   │ Team A: BOWLING                        │   │
│   │                                         │   │
│   │ Score: 0-0 (reset)                     │   │
│   │ → Score runs for Team B                │   │
│   │ → Record wickets                       │   │
│   │ → Track overs (1...20)                │   │
│   └─────────────────────────────────────────┘   │
│                                                 │
│   Result: Team B - 145/7 in 20 overs           │
│   [Click "Finish Match"]                        │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│   MATCH COMPLETED                                │
│                                                  │
│   Innings 1: Team A - 150/8 (20 ov)            │
│   Innings 2: Team B - 145/7 (20 ov)             │
│                                                  │
│   🏆 WINNER: Team A                             │
│   📊 Result: Team A won by 5 runs              │
│                                                  │
│   Match State: completed                        │
└─────────────────────────────────────────────────┘
```

---

## Key System Features

### 1. **Automatic Team Switching**
- System automatically knows which team bats/bowls based on:
  - Toss decision
  - Current innings number (1 or 2)

### 2. **Score Tracking**
- Each innings is tracked separately
- Final comparison uses both innings totals
- System calculates winner automatically

### 3. **Overs Management**
- Each innings has an overs limit (default: 20 overs)
- System stops scoring when limit is reached
- Automatically prompts to complete/finish innings

### 4. **Result Calculation**
The system compares final scores:
```
if (Team A score > Team B score):
    Winner = Team A
    Margin = Team A score - Team B score
    Result = "Team A won by X runs"
    
else if (Team B score > Team A score):
    Winner = Team B  
    Margin = 10 - Team B wickets
    Result = "Team B won by X wickets"
    
else:
    Result = "Match Tied"
```

---

## Admin Actions During Match

### During Innings 1:
1. Select Team A players (batting team)
2. Select Team B players (bowling team)
3. Score runs, wickets, extras
4. When 20 overs complete → Click **"Complete Innings"**

### During Innings 2:
1. Teams automatically swapped
2. Select Team B players (batting team)
3. Select Team A players (bowling team)
4. Score runs, wickets, extras
5. When 20 overs complete → Click **"Finish Match"**

### After Match:
- View complete scorecard with both innings
- See winner and margin
- Match state: `completed`

---

## Example Match Flow

**Match**: India vs Australia

**Toss**: India won, chose to BAT

### Innings 1:
- **Batting**: India
- **Bowling**: Australia
- **Score**: India 150/8 in 20 overs
- Action: Click "Complete Innings"

### Transition:
- System switches teams
- India now bowls
- Australia now bats

### Innings 2:
- **Batting**: Australia
- **Bowling**: India
- **Score**: Australia 145/7 in 20 overs
- Action: Click "Finish Match"

### Result:
```
🏆 WINNER: India
📊 India won by 5 runs

Innings 1: India - 150/8 (20 ov)
Innings 2: Australia - 145/7 (20 ov)
```

---

## Technical Details

### Database Fields:
- `current_innings`: 1 or 2 (tracks which innings)
- `state`: draft → scheduled → live → completed
- Events are tagged with team_id to track which innings they belong to

### Team Determination:
```php
// Innings 1
if (toss_decision == 'bat'):
    batting_team = toss_winner
    bowling_team = other_team

// Innings 2 (automatic swap)
if (toss_decision == 'bat'):
    batting_team = other_team  // opposite of innings 1
    bowling_team = toss_winner
```

---

## Summary

✅ **Team A bats first** → Score tracked  
✅ **Team A completes innings** → Click "Complete Innings"  
✅ **Team B bats second** → Teams automatically swapped  
✅ **Team B completes innings** → Click "Finish Match"  
✅ **System compares scores** → Declares winner automatically  

The system handles team switching, score tracking, and winner calculation automatically! 🏏

