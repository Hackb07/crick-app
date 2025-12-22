---
category: code-quality
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@quality:naming"
source: "Consolidated from RULE 15 (NMS-S)"
---

# Naming Conventions

**Naming Is Not Cosmetic. Naming Is Semantic.**

---

## Core Principle

WHEN naming THEN names must accurately describe behavior.

IF name doesn't match behavior THEN reject.

**Names must align with domain vocabulary. No misleading names, no ambiguous names, no generic names.**

---

## Overview

Naming is semantic, not cosmetic. Names are the first line of documentation. They must:
- Accurately describe behavior
- Align with domain vocabulary
- Be consistent across codebase
- Reveal intent clearly

---

## Semantic Naming Contract

### Required File

**Location**: `/docs/naming/semantic_rules.json`

**Must Define**:
1. **Domain Entity Names** - Reflect domain concepts, use domain vocabulary
2. **Value Object Names** - Reflect value semantics, indicate immutability
3. **Service Verbs** - Indicate action, reflect operation
4. **Event Terms** - Reflect event type, use domain language, versioned
5. **Module Names** - Reflect module purpose, indicate boundaries

---

## Naming Rules

### 1. Domain Entity Names

**Rule**: WHEN naming entity THEN use domain vocabulary.

**Requirements**:
- Must reflect domain concepts
- Must use domain vocabulary
- Must be consistent across codebase
- Must match ubiquitous language

**Examples**:
```php
// ✅ Good: Domain-driven names
class Order { }
class Customer { }
class Invoice { }
class ShoppingCart { }

// ❌ Bad: Generic/technical names
class DataObject { }
class Manager { }
class Handler { }
class Processor { }
```

### 2. Value Object Names

**Rule**: WHEN naming value object THEN indicate value semantics.

**Requirements**:
- Must reflect value semantics
- Must indicate immutability
- Must use domain terms
- Must be descriptive

**Examples**:
```php
// ✅ Good: Value object names
class EmailAddress { }
class Money { }
class DateRange { }
class PhoneNumber { }

// ❌ Bad: Unclear value objects
class Email { } // Is it an entity or value?
class Amount { } // Amount of what?
class Range { } // Range of what?
```

### 3. Service/Function Verbs

**Rule**: WHEN naming service/function THEN use action verbs.

**Requirements**:
- Must indicate action
- Must reflect operation
- Must be consistent
- Must reveal intent

**Examples**:
```php
// ✅ Good: Action verbs
function calculateOrderTotal() { }
function sendConfirmationEmail() { }
function validateCreditCard() { }
function processPayment() { }

// ❌ Bad: Vague or misleading
function doStuff() { }
function handle() { }
function process() { } // Process what?
function manager() { } // Not a verb
```

### 4. Event Names

**Rule**: WHEN naming event THEN reflect event type and use past tense.

**Requirements**:
- Must reflect event type
- Must use domain language
- Must be versioned
- Must use past tense (event already happened)

**Examples**:
```php
// ✅ Good: Event names
class OrderPlaced { }
class PaymentProcessed { }
class UserRegistered { }
class InventoryUpdated { }

// ❌ Bad: Unclear events
class OrderEvent { } // What happened?
class Update { } // Update what?
class Process { } // Not past tense
```

### 5. Module Names

**Rule**: WHEN naming module THEN reflect module purpose.

**Requirements**:
- Must reflect module purpose
- Must use domain concepts
- Must indicate boundaries
- Must be clear and specific

**Examples**:
```php
// ✅ Good: Module names
namespace Billing;
namespace Inventory;
namespace UserManagement;
namespace OrderProcessing;

// ❌ Bad: Generic modules
namespace Core;
namespace Common;
namespace Utils;
namespace Helpers;
```

---

## Naming Patterns

### Variables

**Nouns**:
```php
$user
$orderTotal
$customerEmail
$invoiceDate
```

**Booleans** (prefix with is/has/can):
```php
$isValid
$hasPermission
$canDelete
$isActive
```

**Avoid abbreviations**:
```php
// ✅ Good
$user
$customer
$repository

// ❌ Bad
$usr
$cust
$repo
```

### Functions/Methods

**Use verbs**:
```php
calculateTotal()
sendEmail()
validateInput()
processOrder()
```

**Reveal intent**:
```php
// ✅ Good
getUserById($id)
findActiveCustomers()
createNewOrder()

// ❌ Bad
get($id) // Get what?
find() // Find what?
create() // Create what?
```

**Boolean functions** (prefix with is/has/can):
```php
isValid()
hasAccess()
canDelete()
shouldRetry()
```

### Classes

**Use nouns**:
```php
User
OrderRepository
EmailService
PaymentGateway
```

**Be descriptive**:
```php
// ✅ Good
UserAuthenticationService
OrderPaymentProcessor
EmailNotificationSender

// ❌ Bad
UAS // Abbreviation
OrderProc // Abbreviation
Emailer // Unclear
```

### Constants

**UPPER_SNAKE_CASE**:
```php
MAX_LOGIN_ATTEMPTS
DEFAULT_TIMEOUT_SECONDS
API_BASE_URL
DATABASE_CONNECTION_LIMIT
```

---

## Casing Standards

### 1. Variables & Functions
**Rule**: Use `camelCase`.
- **Variables**: `$user`, `$orderTotal`, `$isValid`
- **Functions**: `calculateTotal()`, `getUserById()`

### 2. Classes & Interfaces
**Rule**: Use `PascalCase`.
- **Classes**: `User`, `OrderRepository`, `PaymentGateway`
- **Interfaces**: `UserRepositoryInterface`, `PaymentProvider`

### 3. Database Fields
**Rule**: Use `snake_case`.
- **Columns**: `user_id`, `created_at`, `first_name`
- **Tables**: `users`, `orders`, `order_items`

### 4. Constants
**Rule**: Use `UPPER_SNAKE_CASE`.
- **Global**: `MAX_RETRY_ATTEMPTS`, `DEFAULT_TIMEOUT`

### 5. Files & Folders
**Rule**: Use `kebab-case`.
- **Files**: `user-controller.ts`, `payment-service.php`
- **Folders**: `/user-management`, `/shared-components`

---

## AST-Level Semantic Enforcement

### CI Validation

**Checks all identifiers**:
- Domain entity names match entity behavior
- Value object names match value semantics
- Service verbs match operations
- Event terms match event types
- Module names match module purposes

### AI Validation

**AI validates**:
- Names match behavior
- Names use domain vocabulary
- Names are consistent
- Names are clear
- No misleading names

**IF behavior ≠ name THEN**:
- PR rejected
- System suggests correct names
- Developer must fix

---

## Cross-Language Naming Harmony

### Consistency Across Languages

**All repos MUST share**:

**Domain Vocabulary**:
- Same entity names across languages
- Same value object names across languages
- Same domain terms across languages

**Error Vocabulary**:
- Same error types across languages
- Same error codes across languages
- Same error messages across languages

**Event Vocabulary**:
- Same event types across languages
- Same event schemas across languages
- Same event names across languages

### Enforcement

IF inconsistency detected THEN:
- Naming drift alert triggered
- Dev freeze initiated
- System blocks merges until harmonized

---

## Anti-Patterns

### ❌ Misleading Names

Names that don't match actual behavior:
```php
// ❌ Bad
function getUserList() {
    return $this->db->query("SELECT * FROM users LIMIT 1"); // Returns single user!
}

// ✅ Good
function getUser() {
    return $this->db->query("SELECT * FROM users LIMIT 1");
}
```

### ❌ Ambiguous Names

Names that could mean multiple things:
```php
// ❌ Bad
class Manager { } // Manages what?
class Handler { } // Handles what?
class Processor { } // Processes what?

// ✅ Good
class OrderManager { }
class PaymentHandler { }
class EmailProcessor { }
```

### ❌ Generic Names

Names without context:
```php
// ❌ Bad
$data
$info
$temp
$obj
$result

// ✅ Good
$orderData
$customerInfo
$temporaryUser
$paymentObject
$validationResult
```

### ❌ Inconsistent Naming

Different names for same concepts:
```php
// ❌ Bad
class User { }
class Customer { } // Same thing?
class Client { } // Same thing?

// ✅ Good - Pick one and stick with it
class Customer { }
class CustomerAccount { }
class CustomerOrder { }
```

### ❌ Non-Domain Names

Names that don't use domain vocabulary:
```php
// ❌ Bad (technical names)
class DataTransferObject { }
class AbstractFactory { }
class SingletonManager { }

// ✅ Good (domain names)
class OrderDetails { }
class PaymentGatewayFactory { }
class ConfigurationManager { }
```

### ❌ File Versioning Disease

**Rule**: Never include version numbers or "status" in filenames. Use Git.

```
// ❌ Bad
script_v1.js
script_final.js
script_final_v2.js
old_script.js

// ✅ Good
script.js (Git handles history)
```

---

## Naming Checklist

Before committing code, verify:

- [ ] All names use domain vocabulary
- [ ] All names match behavior
- [ ] All names are consistent
- [ ] No abbreviations (except standard ones)
- [ ] No generic names (Manager, Handler, Processor without context)
- [ ] Boolean variables/functions use is/has/can prefix
- [ ] Functions use action verbs
- [ ] Classes use nouns
- [ ] Constants use UPPER_SNAKE_CASE
- [ ] Events use past tense
- [ ] Names are clear and reveal intent

---

## Metrics

- **Semantic alignment**: % of names matching behavior (target: 100%)
- **Domain vocabulary usage**: % of names using domain terms (target: >90%)
- **Naming consistency**: % of consistent names across modules (target: 100%)
- **Cross-language harmony**: % of names consistent across languages (target: 100%)
- **Generic name usage**: % of generic names (target: <5%)

---

## Enforcement

- Always apply (P2)
- AST-level semantic analysis in CI
- AI validation of name-behavior alignment
- Cross-language consistency checks
- PR blocked on violations
- System suggests correct names

---

**Related Rules**:
- `@core:clean` - Basic naming in clean code principles
- `@quality:standards` - Code quality includes naming
- `@arch:domain` - Domain modeling uses domain vocabulary
- `@quality:docs` - Documentation reinforces naming
