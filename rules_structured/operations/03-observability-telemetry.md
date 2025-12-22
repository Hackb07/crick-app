---
category: operations
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@ops:observability"
source: "Consolidated from RULE 47 (OBS-S)"
---

# Observability & Telemetry

**You Can't Fix What You Can't See.**

---

## Core Principle

WHEN system runs THEN emit metrics, logs, and traces.

IF incident occurs THEN data must be available to debug.

---

## The Three Pillars

### 1. Logs
- **Purpose**: Discrete events.
- **Rule**: Structured JSON.
- **See**: `@quality:logging`.

### 2. Metrics
- **Purpose**: Aggregatable data (Counters, Gauges).
- **Standard**: Prometheus / OpenMetrics.
- **Key Metrics**: RED (Rate, Errors, Duration).

### 3. Traces
- **Purpose**: Request lifecycle.
- **Standard**: OpenTelemetry.
- **Rule**: Propagate `trace_id` across services.

---

## Enforcement

- **Review**: Ensure new features emit metrics.
- **Ops**: Alert on error spikes.

---

**Related Rules**:
- `@quality:logging` - Logging standards
- `@ops:hardening` - Hardening
