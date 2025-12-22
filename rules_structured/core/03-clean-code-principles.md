---
category: core
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@core:clean"
---

# Clean Code Principles

**DRY + KISS + SOLID + Meaningful Naming**

---

## Core Principle

WHEN writing code THEN follow DRY, KISS, SOLID principles.

IF code is not self-documenting THEN improve naming, not comments.

---

## DRY (Don't Repeat Yourself)

### Rule
IF same logic appears 3+ times THEN extract to function/method/class.

### Application
- WHEN duplicating code THEN ask: "Can this be abstracted?"
- Extract common patterns to utilities
- Use inheritance/composition for shared behavior
- Create reusable components

### Anti-Patterns
- ❌ Copy-paste code blocks
- ❌ Duplicate validation logic
- ❌ Repeated database queries
- ❌ Identical error handling

### Example
```php
// ❌ Bad: Repeated validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception("Invalid email");
}
// ... later ...
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception("Invalid email");
}

// ✅ Good: Extract to function
function validateEmail(string $email): void {
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Invalid email");
    }
}
```

---

## KISS (Keep It Simple, Stupid)

### Rule
WHEN solving problem THEN choose simplest solution that works.

### Application
- Prefer simple over clever
- Avoid premature optimization
- Don't over-engineer
- Clear logic > compact code

### Anti-Patterns
- ❌ Overly complex abstractions
- ❌ Unnecessary design patterns
- ❌ Clever one-liners that obscure intent
- ❌ Over-generalization

### Example
```php
// ❌ Bad: Over-engineered
class UserFactoryBuilderStrategyAdapter {
    // 200 lines of abstraction for simple user creation
}

// ✅ Good: Simple and clear
class UserFactory {
    public function create(array $data): User {
        return new User($data['name'], $data['email']);
    }
}
```

---

## SOLID Principles

### S - Single Responsibility Principle
WHEN class/function has multiple reasons to change THEN split it.

```php
// ❌ Bad: Multiple responsibilities
class User {
    public function save() { /* DB logic */ }
    public function sendEmail() { /* Email logic */ }
    public function generateReport() { /* Report logic */ }
}

// ✅ Good: Single responsibility
class User { /* User data only */ }
class UserRepository { public function save(User $user) {} }
class UserEmailService { public function send(User $user) {} }
class UserReportGenerator { public function generate(User $user) {} }
```

### O - Open/Closed Principle
WHEN adding new behavior THEN extend, don't modify.

```php
// ❌ Bad: Modifying existing code
function calculateDiscount($type) {
    if ($type === 'student') return 0.1;
    if ($type === 'senior') return 0.15;
    // Adding new type requires modifying this function
}

// ✅ Good: Open for extension
interface DiscountStrategy {
    public function calculate(): float;
}
class StudentDiscount implements DiscountStrategy {
    public function calculate(): float { return 0.1; }
}
```

### L - Liskov Substitution Principle
WHEN using inheritance THEN subclass must be substitutable for parent.

```php
// ❌ Bad: Violates LSP
class Bird { public function fly() {} }
class Penguin extends Bird { 
    public function fly() { throw new Exception("Can't fly"); }
}

// ✅ Good: Proper abstraction
interface Bird {}
interface FlyingBird extends Bird { public function fly(); }
class Sparrow implements FlyingBird { public function fly() {} }
class Penguin implements Bird {}
```

### I - Interface Segregation Principle
WHEN defining interface THEN keep it focused and minimal.

```php
// ❌ Bad: Fat interface
interface Worker {
    public function work();
    public function eat();
    public function sleep();
}

// ✅ Good: Segregated interfaces
interface Workable { public function work(); }
interface Eatable { public function eat(); }
interface Sleepable { public function sleep(); }
```

### D - Dependency Inversion Principle
WHEN depending on other code THEN depend on abstractions, not concretions.

```php
// ❌ Bad: Depends on concrete class
class UserService {
    private $db;
    public function __construct() {
        $this->db = new MySQLDatabase(); // Tight coupling
    }
}

// ✅ Good: Depends on abstraction
interface Database { public function query(string $sql); }
class UserService {
    public function __construct(private Database $db) {}
}
```

---

## Meaningful Naming

### Rule
WHEN naming THEN use descriptive, semantic names that reveal intent.

### Application

**Variables**:
- Use nouns: `$user`, `$orderTotal`, `$isActive`
- Boolean: prefix with `is`, `has`, `can`: `$isValid`, `$hasPermission`
- Avoid abbreviations: `$usr` → `$user`

**Functions**:
- Use verbs: `calculateTotal()`, `sendEmail()`, `validateInput()`
- Reveal intent: `getUserById()` not `get()`
- Boolean functions: `isValid()`, `hasAccess()`, `canDelete()`

**Classes**:
- Use nouns: `User`, `OrderRepository`, `EmailService`
- Descriptive: `UserAuthenticationService` not `UAS`

**Constants**:
- UPPER_SNAKE_CASE: `MAX_LOGIN_ATTEMPTS`, `DEFAULT_TIMEOUT`

### Anti-Patterns
- ❌ Single-letter variables (except loop counters)
- ❌ Abbreviations: `calc`, `usr`, `mgr`
- ❌ Generic names: `data`, `info`, `temp`, `obj`
- ❌ Misleading names: `getUserList()` returns single user

### Example
```php
// ❌ Bad naming
function proc($d) {
    $t = 0;
    foreach ($d as $i) {
        $t += $i['p'] * $i['q'];
    }
    return $t;
}

// ✅ Good naming
function calculateOrderTotal(array $orderItems): float {
    $total = 0;
    foreach ($orderItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
```

---

## No Magic Numbers

### Rule
WHEN using numeric/string literals THEN extract to named constants.

### Example
```php
// ❌ Bad: Magic numbers
if ($user->loginAttempts > 3) {
    $user->lockAccount(1800);
}

// ✅ Good: Named constants
const MAX_LOGIN_ATTEMPTS = 3;
const ACCOUNT_LOCK_DURATION_SECONDS = 1800;

if ($user->loginAttempts > MAX_LOGIN_ATTEMPTS) {
    $user->lockAccount(ACCOUNT_LOCK_DURATION_SECONDS);
}
```

---

## Function/Method Size

### Rule
WHEN function exceeds 50 lines THEN consider splitting.

### Guidelines
- One function = one responsibility
- If you need comments to explain sections → extract those sections
- Aim for 10-30 lines per function
- Max cyclomatic complexity: 10

---

## Comments

### Rule
WHEN writing comments THEN explain WHY, not WHAT.

### Good Comments
- ✅ Non-obvious business rules
- ✅ Important invariants
- ✅ Workarounds for external bugs
- ✅ Performance optimizations
- ✅ Security considerations

### Bad Comments
- ❌ Explaining obvious code
- ❌ Commented-out code (use version control)
- ❌ Outdated comments
- ❌ Redundant PHPDoc

### Example
```php
// ❌ Bad: Explains what (obvious)
// Loop through users
foreach ($users as $user) {
    // Send email to user
    sendEmail($user);
}

// ✅ Good: Explains why (non-obvious)
// Send emails in batches to avoid rate limiting (max 100/minute)
foreach (array_chunk($users, 100) as $batch) {
    sendEmailBatch($batch);
    sleep(60);
}
```

---

## Enforcement

- Always apply
- Code review checklist includes clean code principles
- Automated linting for naming conventions
- Complexity metrics in CI

---

**Related Rules**:
- `@quality:standards` - Code quality metrics
- `@quality:naming` - Detailed naming conventions
- `@quality:boilerplate` - Reducing repetition
