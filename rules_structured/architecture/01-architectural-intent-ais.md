---
category: architecture
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@arch:intent"
source: "Consolidated from RULE_1 (AIS-S)"
---

# Architectural Intent (AIS)

**No Code Without Architecture. No Architecture Without Verification.**

---

## Core Principle

WHEN creating/modifying module THEN document architectural intent.

IF module is Critical/Standard tier THEN AIS is mandatory.

**This rule overrides all other rules.**

---

## Requirements

### Architecture Intent Statement (AIS)

**Required for**: All modules (tier-dependent depth)

**Location**: `/docs/ais/<module>.intent.json`

**Schema**: `/docs/schemas/ais-intent.schema.json`

**Required Sections**:

| Section | Required Fields | Enforcement |
|---------|----------------|-------------|
| **Business Intent** | feature goal, KPI impact, success criteria, business owner, non-goals | Binds architecture to product value |
| **Technical Intent** | module purpose, non-responsibilities, domain context, invariants, core business rules | Required for all modules |
| **Boundary Contract** | allowed/forbidden imports, public API, scopes, dependency trust levels | CI validates against dependency graph |
| **Data Contract** | input/output schemas, transformation logic, lineage, retention rules, PII/PCI tagging | Violations = PR failure |
| **Error Model** | error taxonomy, retry/backoff/timeout, compensation logic, fallback behavior | Required for all modules |
| **Security Model** | auth model, permission propagation, data classification, threat model, mitigations | Automated threat-model validator |
| **Performance Envelope** | p95 latency target, throughput capacity, concurrency model, caching policy | CI enforces with microbenchmarks |
| **Operational Envelope** | logs/metrics/alerts, runbook summary, error budget, degradation/rollback strategy | No operational plan = no merge |
| **Backward Compatibility** | API version, compat promises, migration steps, rollback plan | Breakage = PR blocked |
| **Architecture Debt Ledger** | shortcuts, temporary hacks, expiry date, cleanup owner | Debt cannot exist without expiry |

---

## Criticality Tiers

### Critical Tier
**Examples**: Payment, auth/authz, PII/PCI, financial transactions, security boundaries

**Required Files**:
- `/docs/adr/<module>.md` - Architecture Decision Record
- `/docs/ais/<module>.intent.json` - Full AIS
- `/docs/aaia/<module>.aaia.json` - AAIA (if AI-touched)
- `/docs/contracts/<module>.schema.json` - Data contract
- `/docs/debt/<module>.ledger.json` - Debt ledger
- `/docs/threat/<module>.model.json` - Threat model

**AIS Schema**: Full (all sections required)

### Standard Tier
**Examples**: Business logic, API endpoints (non-auth), data transformation, reporting, CRUD

**Required Files**:
- `/docs/adr/<module>.md`
- `/docs/ais/<module>.intent.json` - Lite AIS
- `/docs/aaia/<module>.aaia.json` - AAIA (if AI-touched)
- `/docs/contracts/<module>.schema.json`

**AIS Schema**: Lite (business_intent, technical_intent, boundary_contract, error_model required; security_model simplified; performance/operational optional)

### Experimental Tier
**Examples**: Proof-of-concept, prototypes, research, A/B tests

**Required Files**:
- `/docs/adr/<module>.md` - Minimal ADR

**AIS Schema**: Deferred until promotion to Standard/Critical

---

## AI Architecture Intent Artifact (AAIA)

**Required IF**: Code is AI-generated or AI-assisted

**Location**: `/docs/aaia/<module>.aaia.json`

**Schema**: `/docs/schemas/aaia.schema.json`

**Required Fields**:
- `ai_model_id` - Model used (e.g., "gemini-2.0-flash")
- `prompt` - Full prompt, system instructions, context
- `hallucination_risk_score` - 0-10 score (see calculation below)
- `determinism_signature` - Hash of output for reproducibility
- `file_hashes` - Hashes of generated files
- `ticket_id` - Link to requirement/issue
- `critic_validation` - Secondary AI validation result

**Hallucination Risk Score** (0-10):
```
base_score = 0
IF !syntactic_valid THEN +2
IF !semantic_consistent THEN +3
IF !cross_reference_valid THEN +2
IF test_coverage < 80% THEN +2
IF requires_human_review THEN +1
hallucination_risk_score = min(base_score, 10)
```

**Enforcement**:
- Score >= 7: Requires human architect review
- Score >= 9: Blocks merge automatically

---

## Enforcement

### CI Blockers

PR fails immediately IF:
- AIS missing (for Critical/Standard tier)
- AAIA missing (if AI-touched)
- AIS/AAIA schema invalid
- AIS not linked to ticket ID
- Boundary violations detected
- Invalid data lineage
- Missing threat model (Critical tier)
- Missing invariants
- Missing operational plan
- Migration plan absent
- Performance target missing
- Deterministic signature mismatch
- Primary AI vs critic AI disagreement (high severity)
- Expired architecture exceptions
- AIS contradicts dependency graph
- AIS does not match code behavior

**No override except formal exception** (see Exception System below)

### Runtime Enforcement

**CI instruments**:
- Call stacks
- Event traces
- DI graph
- Network calls

**IF runtime behavior violates AIS THEN deploy blocked**

### Semantic Enforcement

**AI validates**:
- Module purpose alignment
- Domain rule placement
- Use-case alignment
- Layer correctness

**IF misaligned behavior THEN PR fails**

---

## Post-Merge Reconciliation

**Post-merge pipeline**:
1. Rebuild dependency graph
2. Regenerate inferred AIS from code
3. Diff inferred vs declared AIS
4. Recalculate data lineage/threat model
5. Verify invariants/metrics
6. Analyze drift

**IF discrepancies THEN**:
- Auto-create P1 ticket
- Assign module owner
- Deadline: 72 hours
- **IF not fixed THEN auto-block future merges to that module**

**Weekly drift shield**:
- Check AIS vs code drift
- Data lineage drift
- Dependency drift
- Permission drift
- Performance drift
- Threat model drift

**IF mismatch THEN repo freeze**

---

## Exception System

### Standard Exceptions

**Approvers**: Distinguished Engineer + Security Director + SRE Lead

**Location**: `/exceptions/architecture/<id>.json`

**Required Fields**:
- `justification`
- `risk_analysis`
- `compensating_controls`
- `test_plan`
- `rollback_plan`
- `fallback_tests`
- `expiry` (14 days max)
- `review_schedule`

**Expired exceptions break CI immediately**

### Emergency P0 Override

**Triggers**: Production down, security vulnerability exploited, data breach, payment failure

**Approver**: On-call Distinguished Engineer OR SRE Lead

**Duration**: 24 hours

**After 24 hours**: Must create full AIS or PR blocks

**Post-incident review**: Within 48 hours

---

## Legacy Code

**Marked in**: `/docs/modules/index.json` with `legacy: true`

**Grandfathered until**: Touched by PR

**WHEN PR touches legacy THEN**:
1. Auto-generate stub AIS from code analysis
2. Mark all fields as "inferred"
3. PR author must review/validate
4. Update stub to match changes
5. Remove `legacy: true` flag

**Migration exemptions**: 72-hour window to create/update AIS

---

## Prototype & Experimental Code

### Experimental Branch Exemption

**Branches**: `experimental/*`, `prototype/*`, `research/*`

**Rules**:
- Local development: No AIS required
- PR to experimental branch: Minimal ADR only
- PR to main/master: AIS required (no exception)

### Sandbox Mode

**Enable**: `AIS_SANDBOX_MODE=true`

**Allows**:
- Local server runs without AIS validation
- Code generation without AAIA

**Restrictions**:
- Only works for experimental branches
- Cannot push to main/master with sandbox enabled
- Sandbox activity logged for audit

### Prototype Promotion

**Steps**:
1. Create module in `/docs/modules/index.json`
2. Assign tier (Critical/Standard/Experimental)
3. Create full AIS matching tier requirements
4. Migrate code from experimental branch
5. Remove sandbox exemptions

---

## Dependency Trust Levels

| Level | Criteria | Auto-Calculation |
|-------|----------|------------------|
| **Trusted** | Same team, valid AIS with threat model, no security vulnerabilities (90 days), passes all CI, ≥80% test coverage | Same team → trusted (default) |
| **Semtrusted** | Different team but validated, valid AIS, non-critical vulnerabilities, passes most CI, 50-79% test coverage | Cross-team + AIS → semtrusted |
| **Untrusted** | External dependency, no AIS, known vulnerabilities, fails CI, <50% test coverage | External library → untrusted; Security scan failure → downgrade one level; CI failure → downgrade one level |

---

## Performance Baseline Derivation

| Method | Calculation | Freshness |
|--------|-------------|-----------|
| **Historical P95** | Collect p95 from last 30 days, remove outliers (>3σ), baseline = median | <7 days old |
| **Initial Benchmark** | Run microbenchmark suite, baseline = measured p95 | <30 days old |
| **Theoretical** | Algorithm complexity + I/O/network, baseline = theoretical min + 20% margin | <90 days old |
| **SLO Derived** | If SLO exists: baseline = SLO target × 0.8 | No expiry (matches SLO lifecycle) |

---

## Anti-Patterns

- ❌ Code without AIS
- ❌ Drift tolerance (allowing divergence)
- ❌ Exception abuse (no justification/expiry)
- ❌ Legacy bypass (avoiding AIS indefinitely)
- ❌ AI hallucination (accepting without AAIA validation)

---

## Metrics

- **AIS coverage**: % of modules with valid AIS (target: 100%)
- **Drift incidents**: # of drift violations per month (target: 0)
- **Exception rate**: % of PRs requiring exceptions (target: <5%)
- **Hallucination risk**: Average hallucination risk score (target: <5)
- **Enforcement latency**: Time from violation detection to resolution (target: <SLA)

---

## Related Rules

- `@arch:boundary` - Enforced by AIS boundary contracts
- `@arch:drift` - Builds on AIS for drift detection
- `@arch:nfr` - Complements AIS with NFRs
- `@sec:baseline` - References AIS security model
- `@ai:hallucination` - AAIA requirements
- `@quality:errors` - References AIS error model
- `@ops:observability` - References AIS operational envelope

---

**Enforcement**: Always apply. No exceptions without formal approval.
