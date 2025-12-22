---
category: operations
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@ops:perf"
source: "Consolidated from RULE 46 (PBS-S)"
---

# Performance Budget

**Fast by Default. No Regressions.**

---

## Core Principle

WHEN building features THEN respect the performance budget.

IF budget exceeded THEN optimize or reject.

---

## Targets

### 1. Latency (Server)
- **API p95**: < 200ms.
- **DB Query**: < 50ms.

### 2. Frontend (Core Web Vitals)
- **LCP**: < 2.5s.
- **FID**: < 100ms.
- **CLS**: < 0.1.

### 3. Payload
- **JS Bundle**: < 200KB (gzipped).
- **JSON Response**: < 1MB.

---

## Enforcement

- **CI**: Lighthouse score check.
- **Load Testing**: k6 / JMeter in pipeline.

---

**Related Rules**:
- `@ops:observability` - Metrics
- `@arch:nfr` - NFRs
