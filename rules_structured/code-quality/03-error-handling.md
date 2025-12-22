---
category: code-quality
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@quality:errors"
source: "Consolidated from RULE 7 (EH-S) & error-handling-standards.mdc"
---

# Error Handling Standards

**Fail Fast. Fail Safe. Fail Loudly (Internally).**

---

## Core Principle

WHEN an error occurs THEN handle it explicitly.

IF error is recoverable THEN recover. IF NOT THEN fail fast and log context.

---

## Exception Taxonomy

### 1. Domain Exceptions (Business Logic)
- **Use for**: Invalid state, rule violations.
- **Example**: `InsufficientFundsException`, `InvalidEmailException`.
- **Action**: Catch and show user-friendly message.

### 2. Infrastructure Exceptions (System)
- **Use for**: DB down, API timeout, File missing.
- **Example**: `DatabaseConnectionException`, `ApiTimeoutException`.
- **Action**: Retry (if transient) or Fail (500 Error).

### 3. Critical Exceptions (Security/Panic)
- **Use for**: Security breach, corrupted data.
- **Example**: `SecurityBreachException`, `DataCorruptionException`.
- **Action**: Alert immediately (P0).

---

## Handling Patterns

### 1. Fail Fast
**Rule**: Validate state early. Don't process invalid data.
```php
public function process(Order $order) {
    if (!$order->isValid()) throw new InvalidOrderException();
    // ... process
}
```

### 2. No Silent Failures
**Rule**: Never catch an exception and do nothing.
**Anti-Pattern**:
```php
try {
    $db->save();
} catch (Exception $e) {
    // TODO: fix later
}
```

### 3. Contextual Logging
**Rule**: Log the *context*, not just the message.
```php
catch (PaymentFailedException $e) {
    $logger->error("Payment failed", [
        'order_id' => $order->id,
        'amount' => $amount,
        'reason' => $e->getMessage()
    ]);
    throw $e;
}
```

### 4. Global Handler
**Rule**: All uncaught exceptions must go to a global handler that:
1.  Logs full stack trace (internal only).
2.  Returns generic JSON/HTML error to user.
3.  Sets correct HTTP status code (400 vs 500).

---

## Anti-Patterns

- ❌ **Catching `Exception`**: Too broad. Catch specific exceptions.
- ❌ **Exposing Stack Traces**: Never show users raw errors.
- ❌ **Using Exceptions for Flow Control**: Use `if` for expected logic.

---

## Enforcement

- **Static Analysis**: PHPStan checks for unhandled exceptions.
- **Review**: Check for empty catch blocks.

---

**Related Rules**:
- `@quality:logging` - Logging standards
- `@sec:baseline` - Security error handling
