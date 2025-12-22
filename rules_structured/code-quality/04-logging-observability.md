---
category: code-quality
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@quality:logging"
source: "Consolidated from RULE 8 (LOG-S)"
---

# Logging & Observability

**Logs Are Data. Structure Them.**

---

## Core Principle

WHEN logging THEN use structured JSON.

IF log lacks context THEN it is noise.

---

## Logging Standards

### 1. Format
- **Rule**: JSON only in production.
- **Fields**: `timestamp`, `level`, `message`, `context`, `trace_id`.

### 2. Levels
- **DEBUG**: Dev only. Variable states.
- **INFO**: Business events (User logged in).
- **WARN**: Recoverable errors (Retry triggered).
- **ERROR**: Action failed (500 error).
- **FATAL**: System crash.

### 3. Context
- **Rule**: Include IDs (User, Order, Request).
- **Anti-Pattern**: `log.error("Failed")` (No context).
- **Good**: `log.error("Payment Failed", { order_id: 123, reason: "Decline" })`

---

## Enforcement

- **Library**: Use Monolog (PHP) / Winston (Node).
- **Review**: Reject `var_dump` or `console.log` in PRs.

---

**Related Rules**:
- `@quality:errors` - Error handling
- `@ops:observability` - Telemetry
