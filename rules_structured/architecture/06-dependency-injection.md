---
category: architecture
priority: P3
type: ruleset
applies_to: [backend]
always_apply: true
shorthand: "@arch:di"
source: "Consolidated from RULE 6 (DI-S)"
---

# Dependency Injection

**Invert Control. Decouple Components.**

---

## Core Principle

WHEN depending on a service THEN inject it via constructor.

IF using `new Class()` inside logic THEN refactor.

---

## Rules

### 1. Constructor Injection
- **Rule**: Preferred method.
- **Example**: `public function __construct(private Service $service) {}`

### 2. Interface Injection
- **Rule**: Type-hint interfaces, not concrete classes.
- **Example**: `__construct(LoggerInterface $logger)` not `Monolog`.

### 3. Container Usage
- **Rule**: Wire dependencies in the IoC Container (Service Provider).
- **Anti-Pattern**: Service Locator (`Container::get('service')`).

---

## Enforcement

- **Review**: Reject `new` keywords in business logic.
- **Static Analysis**: Check for tight coupling.

---

**Related Rules**:
- `@core:clean` - SOLID (DIP)
- `@arch:boundary` - Boundaries
