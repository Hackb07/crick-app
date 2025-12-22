---
category: architecture
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@arch:nfr"
source: "Consolidated from RULE 2 (NFR-S)"
---

# Non-Functional Requirements (NFR)

**Every NFR Must Be Measurable, Validated, Versioned, Enforced.**

---

## Core Principle

WHEN defining service THEN declare all NFRs with measurable targets.

IF NFR cannot be measured THEN it doesn't exist.

**Non-functional requirements ARE functional requirements.**

---

## Service Classification

### Required Declaration

**File**: `/docs/nfr/service-tier.json`

**Tiers**:
- **TIER_0_CRITICAL** - Customer-facing, revenue-impacting, regulatory-critical
- **TIER_1_CORE** - Core product features with high user visibility
- **TIER_2_SUPPORTING** - Internal tools, admin panels, non-critical features
- **TIER_3_EXPERIMENTAL** - Prototypes, experiments, research projects

**Note**: Tier affects monitoring granularity, NOT enforcement rigor. All tiers must meet NFR standards.

---

## NFR Declaration Matrix

### Required Files Per Service

```
/docs/nfr/
├── service-tier.json          # REQUIRED for all
├── security.nfr.json          # REQUIRED for all
├── performance.nfr.json       # REQUIRED for all
├── reliability.nfr.json       # REQUIRED for all
├── accessibility.nfr.json     # REQUIRED for UI services
├── compliance.nfr.json        # REQUIRED for all
└── exceptions/
    └── <exception-id>.json    # As needed
```

### Each NFR File Must Specify

1. **Measurable targets**
   - Quantitative thresholds
   - Acceptable ranges
   - SLA definitions
   - SLO targets
   - Error budgets

2. **Validation methods**
   - Test frameworks
   - Benchmark suites
   - Monitoring queries
   - Alert definitions
   - Regression tests

3. **Versioning**
   - NFR version
   - Change log
   - Approval chain
   - Effective date
   - Deprecation date

4. **Enforcement rules**
   - CI gates
   - Deployment blockers
   - Runtime monitors
   - Automated rollbacks

---

## NFR Categories

### 1. Security NFR

**File**: `security.nfr.json`

**Required**:
- Authentication (method, session management)
- Authorization (RBAC, permissions)
- Data protection (encryption at rest, encryption in transit)
- Input validation (whitelist, sanitization)
- Security headers (CSP, HSTS, X-Frame-Options)
- Dependency management (vulnerability scanning)
- Audit logging (what, who, when, where)
- CI enforcement (gitleaks, semgrep, snyk, sonarqube)

**Targets**:
- Zero hardcoded secrets
- Zero SQL injection vulnerabilities
- Zero XSS vulnerabilities
- Zero CSRF vulnerabilities
- All dependencies scanned
- All data encrypted in transit (TLS 1.2+)
- All sensitive data encrypted at rest

### 2. Performance NFR

**File**: `performance.nfr.json`

**Required**:
- Latency targets (p50/p95/p99 per endpoint)
- Throughput (requests/second)
- Resource limits (CPU, memory, disk)
- Caching (strategy, TTL, invalidation)
- Database (query time limits, connection pooling)
- Frontend (bundle size, load time)
- Validation (baseline, regression threshold)
- CI enforcement (performance tests)

**Targets**:
- API p95 latency <500ms
- API p99 latency <1s
- Page load time <3s
- Bundle size <500KB
- Database queries <100ms
- No N+1 queries

### 3. Reliability NFR

**File**: `reliability.nfr.json`

**Required**:
- SLO (uptime %, error rate %)
- Error budget (monthly allowance)
- Availability (health check endpoints)
- Resiliency (circuit breaker, retry policy, rate limiting)
- Monitoring (metrics, logging, tracing)
- Alerting (thresholds, escalation)

**Targets**:
- Uptime ≥99.9% (TIER_0), ≥99% (TIER_1)
- Error rate <1%
- MTTR <30 minutes
- Health check response <100ms
- Circuit breaker threshold: 50% errors in 10s
- Retry: exponential backoff, max 3 attempts

### 4. Accessibility NFR

**File**: `accessibility.nfr.json` (UI services only)

**Required**:
- Target standard (WCAG 2.1 Level AA)
- Requirements:
  - Semantic HTML
  - Keyboard navigation (100% coverage)
  - Color contrast (4.5:1 minimum)
  - Alternative text for images
  - Form labels and validation
  - ARIA labels where needed
- Testing (automated: axe-core, manual)
- CI enforcement (accessibility tests)

**Targets**:
- WCAG 2.1 Level AA compliance: 100%
- Keyboard navigation: 100%
- Color contrast: 4.5:1 minimum
- Screen reader compatibility: 100%

### 5. Compliance NFR

**File**: `compliance.nfr.json`

**Required**:
- Frameworks (PCI-DSS, SOC2, GDPR, HIPAA)
- Audit requirements
- Data retention policies
- Compliance checks (automated)

**Targets**:
- PCI-DSS compliance (if handling payments)
- GDPR compliance (if EU users)
- SOC2 compliance (if enterprise)
- Audit trail: 100% coverage for sensitive operations

---

## Multi-Layer Enforcement

### Pre-Commit
- Secret scanning (gitleaks)
- Linting (code style)

### PR Checks (All Block Merge)

**Security**:
- SAST (semgrep, sonarqube)
- DAST (owasp-zap)
- Dependency scan (snyk)
- Container scan (trivy)
- IaC scan (checkov)

**Performance**:
- Performance regression tests
- Load tests (critical paths)
- Bundle size checks
- Database query tests

**Reliability**:
- Unit tests (≥80% coverage)
- Integration tests
- Health check validation

**Accessibility** (UI services):
- Automated audit (axe-core)
- Keyboard navigation tests
- Screen reader compatibility

**Compliance**:
- License compliance
- Data retention validation
- Audit logging verification

### Pre-Deploy
- Smoke tests
- Canary deployment (5% traffic, 15 minutes)
- Auto-rollback on error spike (>5% error rate)

### Periodic
- Load testing (weekly)
- Penetration testing (quarterly)
- Chaos engineering (monthly)
- Accessibility audit (quarterly)

---

## CI Enforcement Checklist

| Category | Checks | Block Condition |
|----------|--------|-----------------|
| **Security** | Auth, encryption, no secrets, no SQLi/XSS/CSRF, dependency scan, container security | Any violation → PR rejected |
| **Performance** | Page load time, API response time, DB query time, memory/CPU limits, bundle size | Regression → PR rejected |
| **Latency** | p50/p95/p99 targets, timeout definitions, circuit breaker thresholds | Exceeds target → PR rejected |
| **Concurrency** | Max concurrent requests, queue depth, thread pool sizes | Exceeds limit → PR rejected |
| **Accessibility** | WCAG 2.1 AA, keyboard nav, screen reader, color contrast, ARIA | Non-compliant → PR rejected |
| **Operational SLOs** | Uptime target, MTTR/MTBF, error rate, success rate | Below target → PR rejected |

---

## Runtime NFR Monitoring

### Metrics (Real-Time)

**Collected**:
- Error rate (1-minute window)
- Latency p50/p95/p99 (1-minute window)
- Throughput (requests/second)
- Dependency health (30-second check)
- Security events (real-time)

### Alerting Thresholds

**Critical (P0)**:
- Service down → immediate alert
- p95 latency >2× target for 10 minutes
- Error rate >5% for 2 minutes → auto-rollback
- Security breach detected

**High (P1)**:
- Error rate >1% for 5 minutes
- p95 latency >target for 10 minutes
- Dependency failure
- WCAG violations detected

### Auto-Remediation

IF error rate >5% for 2 minutes THEN:
1. Trigger auto-rollback
2. Create P0 ticket
3. Notify on-call
4. Block future deployments
5. Escalate to SRE

---

## Exception Handling

### When Exceptions Allowed

**Extremely rare** - only if:
- Principal Engineer + Security + SRE approve
- Exception documented in `/docs/nfr/exceptions/<exception-id>.json`
- Compensating controls implemented
- Monitoring plan active
- Risk assessment completed

### Exception Schema

**Required fields**:
- `exception_id` - Unique identifier
- `service` - Service name
- `nfr_violated` - Which NFR is violated
- `current_value` - Current metric value
- `target_value` - Target metric value
- `justification`:
  - `reason` - Why exception needed
  - `business_impact` - Impact of not granting
  - `mitigation_plan` - How to mitigate risk
- `compensating_controls` - What controls are in place
- `approval`:
  - `requested_by` - Who requested
  - `approved_by` - Who approved
- `duration`:
  - `start_date`
  - `end_date` (max 7 days)
  - `review_frequency` (daily)
- `remediation`:
  - `plan` - How to fix
  - `owner` - Who owns fix
  - `deadline` - When fixed by
- `monitoring`:
  - `metrics_to_track` - What to monitor
- `risk_assessment` - Risk analysis

### Duration Limits

**Maximum**: 7 days  
**Extension**: Requires re-approval (max 1 extension)  
**Absolute maximum**: 14 days  
**Review frequency**: Daily

### Auto-Enforcement

IF exception expires THEN:
- Create P0 ticket
- Block CI pipeline
- Notify principals
- No grace period

---

## Anti-Patterns

- ❌ **NFR as optional** - Treating NFRs as "nice to have"
- ❌ **Unmeasurable targets** - Vague requirements like "fast" or "secure"
- ❌ **Exception abuse** - Creating exceptions without justification
- ❌ **Post-deploy validation only** - No pre-merge gates
- ❌ **Tier-based enforcement variance** - Lower rigor for lower tiers
- ❌ **Missing monitoring** - No runtime validation
- ❌ **Ignoring error budgets** - Exceeding budgets without action

---

## Metrics

- **NFR coverage**: % of services with complete NFR declarations (target: 100%)
- **Violation rate**: # of NFR violations per deployment (target: 0)
- **Exception rate**: % of services with active exceptions (target: <5%)
- **Verification latency**: Time from deployment to NFR verification (target: <verification period)
- **Rollback rate**: % of deployments requiring rollback (target: <1%)

---

## Enforcement

- Always apply (P1)
- CI blocks on violations
- Deployment blocks on violations
- Runtime monitoring enforced
- Auto-rollback on critical violations
- Exception governance enforced

---

**Related Rules**:
- `@arch:intent` - AIS references NFR in operational envelope
- `@sec:baseline` - Complements security NFR
- `@ops:hardening` - Complements operational NFR
- `@ops:perf` - Complements performance NFR
- `@test:coverage` - Supports NFR validation
