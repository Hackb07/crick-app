# 🎯 COMPLETE RULE ENFORCEMENT PROMPT

**Use this prompt to build ANY app/game with FULL rule compliance**

---

## 📋 COPY-PASTE THIS PROMPT

```
@[finalruleset/rules_structured/UNIFIED_RULES.md]
@[finalruleset/rules_structured/PRE_FLIGHT.md]

🚨 MANDATORY ENFORCEMENT - READ CAREFULLY:

I will validate your code with automation. You MUST follow ALL rules.

═══════════════════════════════════════════════════

📋 PRE-FLIGHT CHECKLIST (Complete BEFORE coding):

1. ✅ Read MISTAKES_LOG.md for past errors
2. ✅ Identify applicable rules (@core, @sec, @arch, @quality, @design, @ops, @ai)
3. ✅ Plan architecture (modules, boundaries, dependencies)
4. ✅ Choose tech stack (justify choices)
5. ✅ Estimate complexity and time

═══════════════════════════════════════════════════

🎯 RULES TO ENFORCE (Priority Order):

P1 - CRITICAL (Must Follow):
├─ @core:clean - DRY, KISS, SOLID principles
├─ @sec:baseline - No SQL injection, XSS, hardcoded secrets
├─ @arch:intent - AIS documentation, clear boundaries
├─ @arch:boundary - Module separation, no tight coupling
├─ @ai:corruption - View before edit, single operation per file
└─ @ai:hallucination - AAIA documentation, no fake APIs

P2 - IMPORTANT (Should Follow):
├─ @quality:naming - PascalCase, camelCase, UPPER_SNAKE_CASE
├─ @quality:errors - Try-catch, error logging
├─ @quality:docs - JSDoc/PHPDoc comments
├─ @quality:algorithms - O(n) vs O(1), avoid nested loops
├─ @ops:animation - requestAnimationFrame, not setInterval
├─ @design:principles - Semantic HTML, accessibility
├─ @design:tokens - CSS variables, not hardcoded colors
└─ @test:pyramid - 70% unit, 20% integration, 10% E2E

═══════════════════════════════════════════════════

🎨 DESIGN REQUIREMENTS:

1. ✅ MODERN & PREMIUM - Not basic MVP!
   - Glassmorphism effects
   - Smooth animations
   - Vibrant color palettes
   - Google Fonts (Inter, Roboto, Outfit)

2. ✅ RESPONSIVE - Mobile-first
   - Breakpoints: 640px, 768px, 1024px
   - Touch-friendly controls
   - Viewport meta tag

3. ✅ ACCESSIBLE - WCAG 2.1 AA
   - Semantic HTML
   - ARIA labels
   - Keyboard navigation
   - Screen reader friendly

4. ✅ INTERACTIVE - Engaging UX
   - Hover effects
   - Micro-animations
   - Visual feedback
   - Loading states

═══════════════════════════════════════════════════

💻 CODE REQUIREMENTS:

1. ✅ ARCHITECTURE
   - Separation of concerns (config, logic, UI, effects)
   - Dependency injection
   - Clear module boundaries
   - AIS documentation (@purpose, @module, @dependencies)

2. ✅ SECURITY
   - Input validation
   - Error boundaries
   - No eval() or dangerous functions
   - Frozen configuration objects

3. ✅ CODE QUALITY
   - Complexity < 10 per function
   - Function size < 50 lines
   - Nesting < 3 levels
   - No code duplication
   - No magic numbers (use CONFIG)

4. ✅ PERFORMANCE
   - requestAnimationFrame for animations
   - Avoid O(n²) when O(n) possible
   - Cache expensive calculations
   - No memory leaks (cleanup event listeners)

5. ✅ LOGGING
   - Comprehensive logging system
   - Log all major events
   - Export logs to JSON
   - Save to logs-and-errors/ folder

═══════════════════════════════════════════════════

📁 PROJECT STRUCTURE:

app-name/
├── index.html          ← Semantic HTML
├── css/
│   └── style.css       ← Design tokens, responsive
├── js/
│   ├── config.js       ← All constants (frozen)
│   ├── logger.js       ← Logging system
│   ├── [feature].js    ← Core logic
│   └── main.js         ← Controller/UI
├── assets/             ← Images, fonts
└── README.md           ← Documentation

═══════════════════════════════════════════════════

🎯 TASK:

[YOUR APP/GAME REQUEST HERE]

Example:
"Create a Tetris game with power-ups, achievements, and particle effects"

═══════════════════════════════════════════════════

✅ VALIDATION (I will run after you code):

cd finalruleset/rules_structured/automation
node run-all.js ../../app-name

Expected: ✅ All checks pass (0 critical issues)

═══════════════════════════════════════════════════

📊 DELIVERABLES:

1. ✅ Working app/game (all features)
2. ✅ Modern, premium design
3. ✅ Comprehensive logging
4. ✅ Complete documentation
5. ✅ Creation log in logs-and-errors/validation-reports/
6. ✅ README.md with usage instructions

═══════════════════════════════════════════════════

🚨 CRITICAL REMINDERS:

1. ❌ DON'T create basic/simple designs - Make it WOW!
2. ❌ DON'T use placeholders - Generate real assets
3. ❌ DON'T skip logging - Log everything
4. ❌ DON'T corrupt files - View before edit
5. ✅ DO follow ALL rules - I will validate!

═══════════════════════════════════════════════════

🎯 START CODING!

I acknowledge:
- I will complete PRE_FLIGHT checklist
- I will follow ALL applicable rules
- I will create comprehensive logs
- I will make premium, modern design
- User will validate with automation

Let's build something amazing! 🚀
```

---

## 📝 USAGE INSTRUCTIONS

### Step 1: Copy the Prompt Above

Copy everything from the code block above.

### Step 2: Customize Your Request

Replace this line:
```
[YOUR APP/GAME REQUEST HERE]
```

With your actual request, for example:
- "Create a Tetris game with power-ups"
- "Build a Todo app with drag-and-drop"
- "Make a Calculator with history"
- "Create a Memory card game"
- "Build a Weather dashboard"

### Step 3: Paste to AI

Paste the entire prompt to your AI assistant (Claude, ChatGPT, Gemini, etc.)

### Step 4: Let AI Code

AI will:
1. Complete PRE_FLIGHT checklist
2. Create all files
3. Follow all rules
4. Generate logs
5. Create documentation

### Step 5: Validate

Run automation:
```bash
cd finalruleset/rules_structured/automation
node run-all.js ../../[app-name]
```

### Step 6: Review Logs

Check:
```
logs-and-errors/validation-reports/[app-name]-creation-YYYY-MM-DD-HHMM.md
```

---

## 🎯 EXAMPLE REQUESTS

### Example 1: Tetris Game
```
TASK: Create a Tetris game with:
- Classic gameplay (7 tetrominos)
- Next piece preview
- Score, level, lines cleared
- Power-ups (bomb, slow-mo, ghost)
- Achievements (first line, tetris, combo)
- Particle effects
- Leaderboard (localStorage)
- Modern glassmorphism design
```

### Example 2: Todo App
```
TASK: Create a Todo app with:
- Add, edit, delete, complete tasks
- Categories/tags
- Priority levels (high, medium, low)
- Due dates
- Drag-and-drop reordering
- Filter (all, active, completed)
- Search functionality
- Dark mode toggle
- Export to JSON
- Modern card-based design
```

### Example 3: Memory Game
```
TASK: Create a Memory card game with:
- 4x4 grid (16 cards)
- Difficulty levels (easy, medium, hard)
- Timer and move counter
- High scores
- Themes (animals, emojis, colors)
- Flip animations
- Match effects (particles)
- Sound effects (optional)
- Achievements
- Responsive design
```

---

## ✅ WHAT YOU'LL GET

### Code Quality
- ✅ Clean architecture
- ✅ Zero security issues
- ✅ Perfect naming
- ✅ Comprehensive docs
- ✅ Full logging

### Design Quality
- ✅ Modern, premium look
- ✅ Smooth animations
- ✅ Responsive layout
- ✅ Accessible (WCAG 2.1 AA)
- ✅ Interactive UX

### Documentation
- ✅ README.md
- ✅ Creation log
- ✅ Code comments
- ✅ Usage instructions

---

## 🎯 SUCCESS CRITERIA

**Your app/game is READY when**:

1. ✅ Automation passes (0 critical issues)
2. ✅ Design looks premium (not basic)
3. ✅ All features work
4. ✅ Logs are comprehensive
5. ✅ Documentation is complete

---

**Save this prompt and use it for EVERY new project!** 🚀
