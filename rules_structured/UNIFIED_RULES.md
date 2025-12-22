---
title: "Kavin45$ - Unified Rules (Universal Enterprise)"
version: "2.3.0"
type: "executable_ruleset"
---

# 🚨 ENFORCEMENT PROTOCOL (READ THIS FIRST!)

**CRITICAL**: This file is NOT self-enforcing!

### For AI Assistants:
When user references this file, you MUST:
1. ✅ **Acknowledge enforcement**: "I will enforce these rules and validate with automation"
2. ✅ **Complete PRE_FLIGHT.md**: Check MISTAKES_LOG, identify rules, plan approach
3. ✅ **Code with rules**: Follow @core, @sec, @arch, @quality
4. ✅ **Tell user to validate**: "Run: cd automation && node run-all.js ."
5. ✅ **Fix violations**: If automation finds issues, fix ALL critical ones

### For Users:
After AI codes, ALWAYS run:
```bash
cd automation
node run-all.js .
```
If violations found, paste output and ask AI to fix.

**See ENFORCEMENT_PROTOCOL.md for complete workflow.**

---

# KAVIN45$ UNIFIED RULES (UNIVERSAL)

**Enterprise-Grade Standards for ALL Domains (Web, Game, Systems, AI)**

---

## CORE RULES (ALWAYS APPLY)

### 1. Universal Enterprise Standard
**ALL code must meet ENTERPRISE tier standards regardless of project type.**
- **Architecture**: Modular, Decoupled, Scalable.
- **Security**: Zero Trust, Defense in Depth.
- **Quality**: Production-Ready, Self-Documenting, robust Error Handling.

### 2. Domain Context Switching
```
IF context=REAL_TIME (Games, HFT, Embedded) THEN:
  - PRIORITIZE: Memory stability (Zero GC), CPU cycles, Cache locality
  - ALLOW: Mutable state for performance (with strict encapsulation)
  - AVOID: Object allocation in hot loops
  
IF context=WEB/API (SaaS, E-commerce) THEN:
  - PRIORITIZE: Statelessness, Immutability, Horizontal Scaling
  - ALLOW: Abstractions for maintainability
  - AVOID: Shared state
```

### 3. AI Behavior
```
WHEN editing THEN:
  - ONE file at a time
  - ONLY changed sections
  - NO apologies/preambles
  - NO refactor unrelated code
  - EXACT whitespace matching
  - Minimal diffs

WORKFLOW:
  view_file → identify_section → surgical_edit → explain_rationale
```

### 4. Clean Code (DRY/KISS/SOLID)
```
IF logic_repeated >= 3 THEN extract_to_function
IF solution_complex THEN simplify
IF class_has_multiple_responsibilities THEN split

NAMING:
  variables → descriptive_nouns (user, snakeHead, velocityVector)
  functions → action_verbs (calculateTotal, updatePhysics, renderFrame)
  classes → nouns (User, RigidBody, ParticleSystem)
  constants → UPPER_SNAKE_CASE
  
AVOID: single_letters (except math x,y,z), abbreviations, generic_names (data, temp, obj)

FUNCTION_SIZE: max_50_lines, max_complexity_10
COMMENTS: explain_WHY not_WHAT
NO_MAGIC_NUMBERS: extract_to_constants (CONFIG object)
```

### 5. Coding Workflow
```
BEFORE code THEN:
  0. check_MISTAKES_LOG.md (Learn from past errors)
  1. understand_requirements (inputs, outputs, edge_cases)
  2. identify_domain_constraints (FPS budget vs Latency budget)
  3. design_structure (architecture, data_structures, interfaces, flow)
  4. create_plan (files, functions, dependencies, tests)
  5. implement_code
  6. validate (requirements_met, tests_pass, no_regressions)
  
WHEN mistake_occurs THEN:
  - Log to MISTAKES_LOG.md immediately
  - Include: Date, Context, Root Cause, Prevention
```

---

## ARCHITECTURE RULES

### Project Structure
```
/src        → Application Logic (modules, services, engine)
/tests      → Mirrors /src structure
/assets     → Static resources (images, sounds, public)
/docs       → Documentation & ADRs
/infra      → Docker, Terraform, Build Scripts

RULE: No logic outside /src. Files must be kebab-case.
```

### Architectural Intent (AIS)
```
ALWAYS document_AIS:
  - module_purpose
  - public_contracts (APIs, interfaces)
  - dependencies (what_it_needs, what_needs_it)
  - data_flow
  - error_handling_strategy
  - performance_requirements (FPS target / Latency target)
  - security_considerations
```

### Boundary Enforcement
```
NO circular_dependencies
NO cross_layer_violations (presentation → business → data)
NO tight_coupling

DEPENDENCIES:
  depend_on_abstractions NOT concretions
  use_dependency_injection (or Service Locator for Games)
  minimize_coupling
```

### Database & State Management
```
MIGRATIONS: Version-controlled Schema Changes
RULE: No manual state mutations in production
RULE: Migrations/State-Transitions must be deterministic
```

---

## SECURITY RULES (ENTERPRISE BASELINE)

### 1. Web & API Security (OWASP Top 10)
```
1. CSRF: token_on_POST/PUT/DELETE (Anti-Forgery Tokens)
2. XSS: htmlspecialchars() on_output, json_encode() for_JS, Content-Security-Policy (CSP)
3. SQL_INJECTION: prepared_statements (PDO/mysqli) ONLY. No string concatenation.
4. SESSION: secure_flag, httponly_flag, regenerate_on_login, SameSite=Strict
5. HEADERS: X-Frame-Options, X-Content-Type-Options, HSTS
6. FILE_UPLOAD: validate_type (MIME), validate_size, random_filename, store_outside_webroot
```

### 2. General / System Security
```
1. INPUT_VALIDATION: Strict typing + Range checks + Whitelist (Allow-list)
2. SECRETS: Env vars only. Never commit secrets. Validate at startup.
3. AUTHENTICATION: Strong Identity (Bcrypt/Argon2) + MFA + Rate Limiting
4. AUTHORIZATION: Least Privilege (RBAC/ABAC). Deny by default.
5. DATA_PROTECTION: Encrypt PII at Rest (AES-256) + TLS 1.3 in Transit
6. ERROR_HANDLING: Fail Safe. No stack traces to user. Log to secure file.
7. DEPENDENCIES: Scan for CVEs (npm audit / composer audit). Pin versions.
```

### 3. Compliance & Audit
```
AUDIT_TRAIL:
  - LOG: Who (User/IP), What (Action), When (UTC), Where (Resource), Why
  - RULE: Critical actions (Create/Update/Delete) MUST have immutable audit logs
  - STORAGE: Append-only, separate from app logs
```

---

## CODE QUALITY RULES

### Quality Standards
```
COMPLEXITY: cyclomatic <= 10
FUNCTION_SIZE: <= 50_lines
CLASS_SIZE: <= 300_lines
NESTING: <= 3_levels
DUPLICATION: <= 3%
```

### Naming Conventions
```
CLASSES: PascalCase (UserService, RigidBody)
FUNCTIONS: camelCase (getUserById, updatePhysics)
VARIABLES: camelCase (userId, velocityVector)
CONSTANTS: UPPER_SNAKE_CASE (MAX_ATTEMPTS, GRAVITY_CONSTANT)
DATABASES: snake_case (user_id, created_at)
BOOLEANS: is/has/can prefix (isActive, hasCollision)
FILES: kebab-case (user-service.ts, game-engine.js)
```

### Documentation Standards
```
HIERARCHY:
  1. Code (Self-documenting, Semantic Naming)
  2. JSDoc/DocBlock (Required for ALL public interfaces)
  3. Module README (Required for every module/component)
  4. Architecture Decision Records (ADR) for major choices
```

### Error Handling
```
VALIDATION_ERRORS: return_to_user with_details
BUSINESS_ERRORS: log + return_generic_message
SYSTEM_ERRORS: log + alert + return_500/Crash_Gracefully

HIERARCHY:
  RecoverableException → user_fixable
  FatalException → system_failure (requires restart/alert)

NO silent_failures
NO generic_catch_all without_logging
```

---

## TESTING RULES

### Test Pyramid
```
DISTRIBUTION: 70% unit, 20% integration, 10% E2E
COVERAGE: >= 80% overall, 100% for_critical_paths
PATTERN: Arrange-Act-Assert

UNIT: test_single_function, mock_dependencies
INTEGRATION: test_multiple_components, real_dependencies
E2E: test_user_workflows, real_system
```

### Test Quality
```
TESTS_MUST:
  - be_independent (no_shared_state)
  - be_deterministic (no_random, no_time_dependent)
  - be_fast (unit < 100ms, integration < 1s)
  - be_readable (clear_intent)
  - test_one_thing

NO flaky_tests
NO commented_tests
NO tests_that_always_pass
```

### Regression Prevention
```
WHEN bug_found THEN:
  1. write_failing_test
  2. fix_bug
  3. verify_test_passes
  4. commit_test_with_fix
```

---

## AI GOVERNANCE RULES

### Temperature Policy
```
auth/payments → 0.05 (deterministic)
backend_logic → 0.1 (consistent)
frontend_UI → 0.2 (some_creativity)
brainstorming → 0.5-0.7 (exploratory)
```

### Hallucination Prevention (AAIA)
```
IF AI_generated_code THEN create_AAIA

AAIA_CONTAINS:
  - what_AI_generated
  - assumptions_made
  - validation_performed
  - risk_score (1-10)
  - human_review_required (if_risk >= 7)

CRITIC_VALIDATION: second_AI_reviews_first_AI_output
```

### Semantic Review
```
VERIFY:
  - intent_preserved
  - logic_correct
  - edge_cases_handled
  - no_hallucinated_APIs
  - no_deprecated_patterns
```

---

## OPERATIONS & PERFORMANCE

### Performance Budget (Context-Aware)
```
IF context=WEB THEN:
  - API_LATENCY: p95 < 200ms
  - PAGE_LOAD: < 2s
  
IF context=GAME/REAL_TIME THEN:
  - FRAME_TIME: < 16ms (60fps) or < 8ms (120fps)
  - GC_PAUSE: < 1ms
  - MEMORY_CHURN: 0 bytes/frame in hot loops
```

### Observability
```
METRICS: throughput, error_rate, latency/frame_time, saturation
LOGS: structured_JSON, centralized
ALERTS: error_rate > 1%, latency_p99 > threshold
```

### Incident Response
```
WHEN incident THEN:
  1. detect (monitoring/alerts)
  2. respond (on-call engineer)
  3. mitigate (rollback/hotfix)
  4. resolve (root_cause_fix)
  5. postmortem (RCA + prevention)

RCA_TEMPLATE: what_happened, why_happened, how_prevent
```

---

## WORKFLOW RULES

### CI/CD Pipeline
```
STAGES:
  1. lint → code_style
  2. test → unit + integration
  3. security → SAST + dependency_scan
  4. build → compile + package
  5. deploy → staging → production

GATES:
  - tests_pass
  - coverage >= 80%
  - no_critical_vulnerabilities
  - manual_approval_for_production
```

### Git Workflow
```
BRANCHES:
  main → production_ready
  develop → integration
  feature/* → new_features
  fix/* → bug_fixes
  hotfix/* → production_fixes

COMMITS: type(scope): description
  feat, fix, refactor, docs, chore, test

MERGE: squash_feature_branches, preserve_main_history
```

### Code Review
```
CHECKLIST:
  - [ ] logic_correct
  - [ ] tests_included
  - [ ] security_checked
  - [ ] performance_acceptable
  - [ ] documentation_updated
  - [ ] no_breaking_changes (or_documented)

APPROVAL: >= 1_reviewer for_merge
```

### Technical Debt
```
TRACK: /docs/debt/ledger.md
LOG: what, why, cost (High/Med/Low), deadline
REVIEW: quarterly
PAYDOWN: allocate_20%_sprint_capacity
```

---

## CONFLICT RESOLUTION

### First Principles
```
1. CORRECTNESS > performance > elegance
2. SECURITY > convenience
3. SIMPLICITY > cleverness
4. MAINTAINABILITY > initial_development_speed
5. EXPLICIT > implicit
6. FAIL_FAST > silent_failure
```

---

## ENFORCEMENT

### Always Apply (P1)
```
@core (mindset, ai, clean, workflow)
@arch (intent, boundary)
@sec (baseline, auth, privacy, secrets)
@ai (safety, hallucination, review)
```

### Context-Dependent (P2)
```
@quality (standards, naming, errors, logging, docs)
@test (pyramid, coverage, quality, regression)
@ops (hardening, perf, observability, incident)
```

### On-Demand (P3)
```
@flow (cicd, git, review, debt, release)
```

---

## AUTOMATION

### Real Automation (Active)
```
1. AI Feedback Loop:
   - Check MISTAKES_LOG.md before every task
   - Log mistakes immediately

2. Compliance Audits:
   - Run: Review logs-and-errors/compliance-audit-*/
   - Frequency: Weekly

3. Use Standard Tools:
   - npm audit (security)
   - npm test (testing)
   - eslint (linting)
```

---

**COMPRESSION RATIO**: 43 files (~500KB) → 1 file (~12KB) = 97% reduction
**FUNCTIONAL LOSS**: 0% (all essential rules preserved)
**AMBIGUITY**: 0% (all rules executable)

**STATUS**: ✅ UNIVERSAL ENTERPRISE STANDARD
