# Cricket Scorer Interface - Design Specification

## Overview
Modern, mobile-first scorer interface optimized for fast, intuitive match scoring with minimal clicks and maximum visual clarity.

---

## Three-Panel Layout

### **TOP PANEL: Sticky Header** (Dark Gradient Background)
Always visible at the top of the screen as the user scrolls.

#### Components:

1. **Header Actions**
   - ☰ Menu Button (left)
   - Team Names: "Mumbai vs Delhi" (center)
   - ✕ Close Button (right)

2. **Match Score Display**
   ```
   120/4 (15.5)
   CRR: 7.74  Need: 31  RRR: 6.89
   ```
   - Large, bold score numbers
   - Overs in parentheses
   - Meta stats in smaller text below

3. **Batsmen Status** (2-column grid)
   
   **Striker Card** (Blue highlight):
   ```
   🏏 STRIKER
   S. Khan
   45*(28)
   ```
   
   **Non-Striker Card**:
   ```
   👟 NON-STRIKER
   V. Sharma
   12(10)
   ```
   
   Each card has a ⋮ button for changing players

4. **Bowler Status**
   ```
   🎾 BOWLER
   J. Ali
   3.5 - 0 - 20 - 1
   (Overs - Maidens - Runs - Wickets)
   ```

---

### **MIDDLE PANEL: Context & Feedback** (Light Gray Background)

#### 1. This Over Section
Displays ball-by-ball tracking of the current over:

```
THIS OVER
○ ① W ④ • ⑥ ②
```

Ball colors:
- **Gray dot (•)**: Dot ball (0 runs)
- **Blue circle**: Runs scored (1, 2, 3)
- **Green circle (④)**: Four
- **Orange circle (⑥)**: Six
- **Red circle (W)**: Wicket
- **Dashed circle**: Extra (WD, NB, BYE, LB)

#### 2. Wagon Wheel Section
**Visual cricket field diagram**:
- Green boundary circle
- Pitch in center with creases
- Field positions labeled (Cover, Point, Long On, Mid Wicket, etc.)
- Clickable zones for boundary tracking

**UX Flow**:
1. Scorer presses "4" or "6" button
2. Modal/prompt appears: "Where did the ball go?"
3. Scorer taps the field zone
4. Data saved with shot direction

---

### **BOTTOM PANEL: Action Keypad** (Fixed at Bottom)

#### 1. Boundary Buttons (LARGEST - Full Width Priority)
```
┌─────────────────┐   ┌─────────────────┐
│        4        │   │        6        │
│      FOUR       │   │       SIX       │
└─────────────────┘   └─────────────────┘
```
- **Green gradient** for 4
- **Orange gradient** for 6
- **Huge, easy-to-hit** buttons
- **80px minimum height**

#### 2. Standard Run Buttons
```
┌────┐ ┌────┐ ┌────┐ ┌────┐
│ 0  │ │ 1  │ │ 2  │ │ 3  │
└────┘ └────┘ └────┘ └────┘
```
- White cards with gray borders
- **60px height**
- **4-column grid**

#### 3. Extra Buttons (2x2 Grid)
```
┌─────────┐  ┌─────────┐
│   WD    │  │   NB    │
│  Wide   │  │ No Ball │
└─────────┘  └─────────┘
┌─────────┐  ┌─────────┐
│   BYE   │  │   LB    │
│   Bye   │  │ Leg Bye │
└─────────┘  └─────────┘
```
- Orange tinted background
- **56px height**
- Shows confirmation modal if extra + runs

#### 4. Special Action Buttons
```
┌──────────────┐  ┌──────────────┐
│     WKT      │  │      ↺       │
│   Wicket     │  │     UNDO     │
└──────────────┘  └──────────────┘
```
- **WKT**: Red, prominent, separated
- **UNDO**: Gray, requires confirmation
- **64px height**

#### 5. Innings Control (if Innings 1)
```
┌─────────────────────┐
│   End Innings 1     │
└─────────────────────┘
```
- Red gradient button
- Centered at bottom

---

## Design Principles

### 1. **Hierarchy**
- **Most frequent**: Boundaries (4/6) = Largest
- **Frequent**: Runs (0-3) = Large
- **Less frequent**: Extras = Medium
- **Rare**: Wicket, Undo = Separate, clearly marked

### 2. **Color Coding**
- **Green**: Positive (Fours, Success)
- **Orange**: Warning/Attention (Sixes, Extras)
- **Red**: Critical (Wickets, Danger)
- **Blue**: Primary actions
- **Gray**: Neutral (Dot balls, Undo)

### 3. **Touch Optimization**
- **Minimum 44px touch targets**
- **Generous spacing** between buttons
- **Large hit areas** for most-used actions
- **Rounded corners** to prevent mis-taps

### 4. **Visual Feedback**
- **Hover effects**: Lift buttons on hover
- **Active states**: Scale down on press
- **Shadows**: Depth perception
- **Transitions**: Smooth, 200ms

### 5. **Information Architecture**
- **Sticky header**: Always see score and players
- **Middle panel**: Context for decision-making
- **Bottom keypad**: Actions always accessible

---

## Responsive Behavior

### Mobile (< 480px)
- Batsmen in single column
- Boundaries in single column
- Runs in 2x2 grid

### Tablet (768px - 1023px)
- Middle panel: 2-column grid (Over + Wagon Wheel side-by-side)
- All buttons maintain size

### Desktop (>= 1024px)
- Action keypad max-width 800px, centered
- Optimal layout maintained
- No stretching of buttons

---

## Accessibility

- **ARIA labels** on all interactive elements
- **Semantic HTML** structure
- **Keyboard navigation** support
- **High contrast** ratios (WCAG AA compliant)
- **Screen reader** friendly

---

## Technical Implementation

### Files Modified:
1. **scorer.php**: Restructured HTML layout
2. **scorer-enhanced.css**: Complete styling system

### CSS Variables:
```css
--scorer-primary: #2563eb (blue)
--scorer-success: #10b981 (green)
--scorer-warning: #f59e0b (orange)
--scorer-danger: #ef4444 (red)
```

### Key Classes:
- `.scorer-header-sticky`: Top panel
- `.scorer-middle-panel`: Context section
- `.scorer-action-keypad`: Bottom actions
- `.btn-boundary`: Large 4/6 buttons
- `.btn-run`: Standard run buttons
- `.btn-extra`: Extra buttons
- `.btn-action`: Special actions

---

## Usage Flow

### Recording a Boundary:
1. Tap large "4" or "6" button
2. (Optional) Wagon wheel prompt appears
3. Tap field zone where ball went
4. Ball recorded with direction data

### Recording Runs:
1. Tap "0", "1", "2", or "3" button
2. Immediately recorded
3. Shows in "This Over" tracker

### Recording Extras:
1. Tap "WD", "NB", "BYE", or "LB"
2. Modal appears if extra runs possible
3. Confirm or add runs off the bat

### Recording Wicket:
1. Tap red "WKT" button
2. Modal shows dismissal types
3. Select type (Bowled, Caught, LBW, etc.)
4. New batsman selection prompted

### Undo Last Ball:
1. Tap "↺ UNDO" button
2. Confirmation dialog appears
3. Confirm to revert match state

---

## Future Enhancements

1. **Offline Mode**: Service worker for offline scoring
2. **Live Sync**: Real-time updates to public scorecard
3. **Ball Tracking**: Advanced wagon wheel with lines
4. **Voice Commands**: "Four" to record boundary
5. **Gesture Controls**: Swipe patterns for common actions
6. **Commentary**: Quick comment templates
7. **Analytics**: Real-time scoring speed metrics

---

## Summary

This interface dramatically improves the scoring experience by:
- ✅ Reducing clicks for most common actions
- ✅ Providing immediate visual feedback
- ✅ Maintaining context with sticky header
- ✅ Optimizing for one-handed mobile use
- ✅ Following cricket scoring mental models
- ✅ Supporting advanced features like wagon wheel
- ✅ Maintaining professional, modern aesthetics

The design prioritizes **speed**, **accuracy**, and **ease of use** for live cricket scoring.
