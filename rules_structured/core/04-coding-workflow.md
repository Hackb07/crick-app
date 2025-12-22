---
category: core
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@core:workflow"
---

# Coding Workflow

**Structured Thinking: Requirements → Constraints → Structure → Plan → Code**

---

## Core Principle

WHEN starting new feature THEN think before coding.

IF jumping straight to code THEN stop and plan first.

---

## Workflow Steps

### 1. Understand Requirements

BEFORE writing code THEN answer:
- What is the user trying to achieve?
- What are the inputs and outputs?
- What are the success criteria?
- What are the edge cases?
- What are the non-functional requirements (performance, security, etc.)?

### 2. Identify Constraints

BEFORE designing THEN identify:
- Technical constraints (language, framework, libraries)
- Business constraints (budget, timeline, resources)
- Performance constraints (latency, throughput, scalability)
- Security constraints (auth, data privacy, compliance)
- Integration constraints (APIs, databases, third-party services)

### 3. Design Structure

BEFORE implementing THEN design:
- High-level architecture (layers, modules, components)
- Data structures (entities, DTOs, value objects)
- Interfaces and contracts (APIs, repositories, services)
- Data flow (request → processing → response)
- Error handling strategy

### 4. Create Plan

BEFORE coding THEN outline:
- Files to create/modify
- Functions/classes to implement
- Dependencies to add
- Tests to write
- Migration/deployment steps

### 5. Implement Code

WHEN coding THEN:
- Follow the plan
- Write tests alongside code (TDD if appropriate)
- Commit frequently with meaningful messages
- Refactor as you go (keep code clean)
- Document non-obvious decisions

### 6. Validate

AFTER coding THEN verify:
- All requirements met
- All edge cases handled
- All tests passing
- Code quality standards met
- No regressions introduced

---

## Bug Fixing Protocol

WHEN fixing an error THEN follow:

### 1. Root Cause & Scope
- **Rule**: Identify the root cause first.
- **Rule**: Update EVERY occurrence in the project (don't fix just one spot).
- **Rule**: Never patch a single line without checking related types, imports, and call sites.

### 2. Implementation
- **Rule**: Apply the smallest change that eliminates the pattern everywhere.
- **Rule**: Do not create new abstractions unless required by the root cause.

### 3. Verification
- **Rule**: After a fix, compile/test and continue until the entire error chain is resolved.

---

## Architecture Thinking

### Before Coding, Ask:

**Responsibility**:
- What is this component responsible for?
- What is it NOT responsible for?
- Is this a single, clear responsibility?

**Dependencies**:
- What does this depend on?
- What depends on this?
- Are dependencies minimized?
- Are dependencies abstracted (interfaces)?

**Boundaries**:
- What are the module boundaries?
- What is the public API?
- What is internal implementation?

**Data Flow**:
- How does data enter the system?
- How is data transformed?
- How does data exit the system?
- Where is data validated?

**Error Handling**:
- What can go wrong?
- How should errors be handled?
- What should be logged?
- What should be returned to user?

**Performance**:
- What are the performance requirements?
- What are the bottlenecks?
- How can this scale?

---

## Decision Framework

WHEN making design decision THEN consider:

### 1. Simplicity
- Is this the simplest solution that works?
- Can it be simpler without losing functionality?

### 2. Maintainability
- Will the next developer understand this?
- Is it easy to modify/extend?
- Is it testable?

### 3. Performance
- Does this meet performance requirements?
- Are there obvious bottlenecks?
- Is optimization needed now or later?

### 4. Security
- Are inputs validated?
- Are outputs sanitized?
- Are secrets protected?
- Is auth/authz enforced?

### 5. Scalability
- Will this work at 10x scale?
- What breaks first?
- How can it be scaled?

---

## Examples

### Example 1: User Registration

**1. Requirements**:
- User provides email, password, name
- Email must be unique
- Password must be hashed
- Send confirmation email
- Return user ID on success

**2. Constraints**:
- PHP 7.4+, MySQL
- Session-based auth
- Email via SMTP
- GDPR compliance (data privacy)

**3. Structure**:
```
Controller (UserController)
  ↓
Service (UserRegistrationService)
  ↓
Repository (UserRepository)
  ↓
Database (users table)

EmailService (separate concern)
```

**4. Plan**:
- Create `UserRegistrationService`
- Add `register()` method to `UserRepository`
- Validate email uniqueness
- Hash password with `password_hash()`
- Insert user record
- Send confirmation email (async if possible)
- Return user ID

**5. Implement**:
```php
class UserRegistrationService {
    public function __construct(
        private UserRepository $userRepo,
        private EmailService $emailService
    ) {}

    public function register(string $email, string $password, string $name): int {
        // Validate
        if ($this->userRepo->emailExists($email)) {
            throw new DuplicateEmailException();
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Create user
        $userId = $this->userRepo->create($email, $hashedPassword, $name);

        // Send confirmation email (async)
        $this->emailService->sendConfirmation($email, $name);

        return $userId;
    }
}
```

**6. Validate**:
- Test: email uniqueness check
- Test: password hashing
- Test: user creation
- Test: email sending
- Test: error handling (duplicate email, DB failure, email failure)

---

### Example 2: Match Scoring System

**1. Requirements**:
- Record runs, wickets, extras
- Track striker/non-striker
- Handle innings change
- Calculate live score
- Validate events (can't score after match ends)

**2. Constraints**:
- Real-time updates (WebSocket or polling)
- Mobile-first UI
- Low latency (<500ms)
- Data consistency (no lost events)

**3. Structure**:
```
Admin UI (score.php)
  ↓
API (score-api.php)
  ↓
MatchScoringService
  ↓
MatchRepository, EventRepository
  ↓
Database (matches, events)
```

**4. Plan**:
- Create `MatchScoringService`
- Add `recordEvent()` method
- Validate match state (not finished)
- Insert event record
- Update match aggregates (runs, wickets, overs)
- Return updated score

**5. Implement**:
```php
class MatchScoringService {
    public function recordEvent(int $matchId, string $eventType, array $data): array {
        // Validate match not finished
        $match = $this->matchRepo->getById($matchId);
        if ($match->isFinished()) {
            throw new MatchFinishedException();
        }

        // Record event
        $this->eventRepo->create($matchId, $eventType, $data);

        // Update aggregates
        $this->updateMatchAggregates($matchId);

        // Return updated score
        return $this->matchRepo->getScore($matchId);
    }
}
```

**6. Validate**:
- Test: event recording
- Test: match finished validation
- Test: aggregate updates
- Test: concurrent events (race conditions)
- Test: error handling

---

## Anti-Patterns

- ❌ Coding before understanding requirements
- ❌ Skipping design phase
- ❌ No plan, just "figure it out as I go"
- ❌ Ignoring constraints
- ❌ Not thinking about edge cases
- ❌ No validation step

---

## Enforcement

- Always apply
- Code review checks for evidence of planning
- Architecture review for complex features

---

**Related Rules**:
- `@core:mindset` - Tier-based delivery
- `@arch:intent` - Architecture intent statements
- `@quality:standards` - Code quality metrics
