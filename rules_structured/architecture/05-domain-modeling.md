---
category: architecture
priority: P1
type: ruleset
applies_to: [backend]
always_apply: true
shorthand: "@arch:domain"
source: "Consolidated from RULE 3 (FS-S) & RULE 4 (BLS-S)"
---

# Domain Modeling

**Logic Lives in the Domain. Not in Controllers. Not in UI.**

---

## Core Principle

WHEN implementing business logic THEN place it in the Domain Layer.

IF logic is reusable THEN it belongs in a Service or Entity.

---

## Domain Driven Design (DDD) Lite

### 1. Entities
- **Definition**: Objects with identity (ID).
- **Rule**: Encapsulate state and behavior.
- **Example**: `User`, `Order`.
- **Anti-Pattern**: Anemic Domain Model (Getters/Setters only).

### 2. Value Objects
- **Definition**: Objects defined by attributes, immutable.
- **Rule**: No identity. Equality by value.
- **Example**: `EmailAddress`, `Money`, `DateRange`.

### 3. Aggregates
- **Definition**: Cluster of objects treated as a unit.
- **Rule**: Access only via Aggregate Root.
- **Example**: `Order` (Root) contains `OrderItems`.

### 4. Domain Services
- **Definition**: Logic that doesn't fit one entity.
- **Rule**: Stateless operations.
- **Example**: `PaymentProcessor`, `PricingCalculator`.

---

## Layer Rules

### Domain Layer
- **Contains**: Entities, Value Objects, Domain Services, Repository Interfaces.
- **Dependencies**: ZERO. Pure PHP/JS logic.

### Application Layer
- **Contains**: Use Cases, Command Handlers.
- **Role**: Orchestrates domain objects.

### Infrastructure Layer
- **Contains**: DB Implementations, API Clients.
- **Role**: Implements interfaces defined in Domain.

---

## Enforcement

- **Review**: Reject logic in Controllers/Views.
- **Architecture**: Enforce dependency direction (Infra -> Domain).

---

**Related Rules**:
- `@arch:boundary` - Layer boundaries
- `@quality:naming` - Domain vocabulary
