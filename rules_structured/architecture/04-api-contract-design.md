---
category: architecture
priority: P1
type: ruleset
applies_to: [backend]
always_apply: true
shorthand: "@arch:api"
source: "Consolidated from RULE 10 (API-S)"
---

# API Contract Design

**Contracts Are Forever. Design With Intent.**

---

## Core Principle

WHEN designing API THEN define explicit contract first.

IF contract changes THEN version it. NO BREAKING CHANGES without major version bump.

---

## Contract Rules

### 1. RESTful Standards
- **Resources**: Nouns (`/users`, `/orders`).
- **Actions**: HTTP Verbs (`GET`, `POST`, `PUT`, `DELETE`).
- **Status Codes**: Use correct codes (`200`, `201`, `400`, `401`, `403`, `404`, `500`).

### 2. JSON Envelope
- **Rule**: Consistent response structure.
```json
{
  "data": { ... },
  "meta": { "page": 1, "total": 100 },
  "error": null
}
```

### 3. Versioning
- **Strategy**: URL Versioning (`/v1/users`).
- **Rule**: Breaking change = New Version.
- **Breaking**: Removing field, changing type, adding required param.
- **Non-Breaking**: Adding optional field, adding new endpoint.

### 4. Idempotency
- **Rule**: `GET`, `PUT`, `DELETE` must be idempotent.
- **Rule**: `POST` is not idempotent (use `Idempotency-Key` header for critical actions).

---

## Schema Validation

- **Input**: Validate strict schema (reject unknown fields).
- **Output**: Serialize strictly (don't leak internal fields).
- **Tool**: JSON Schema / OpenAPI.

---

## Enforcement

- **Design**: API Spec required before code.
- **Testing**: Contract tests (Consumer-Driven Contracts).
- **CI**: Breaking change detection (OpenAPI diff).

---

**Related Rules**:
- `@arch:boundary` - Boundary enforcement
- `@quality:errors` - API error responses
