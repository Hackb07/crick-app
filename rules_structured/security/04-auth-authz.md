---
category: security
priority: P1
type: ruleset
applies_to: [backend]
always_apply: true
shorthand: "@sec:auth"
source: "Consolidated from Auth Rules"
---

# Authentication & Authorization

**Verify Identity. Enforce Permissions. Zero Trust.**

---

## Core Principle

WHEN accessing resources THEN verify Identity (AuthN) and Permission (AuthZ).

IF check fails THEN deny access immediately.

---

## Authentication (AuthN)

### 1. Multi-Factor Authentication (MFA)
- **Rule**: Required for admin/privileged accounts.
- **Standard**: TOTP (Time-based One-Time Password).

### 2. Session Management
- **Rule**: Stateless (JWT) or Stateful (Session ID).
- **JWT**: Short-lived (15m) + Refresh Token.
- **Session**: HttpOnly, Secure cookies.

### 3. Password Policy
- **Rule**: Min 12 chars. No complexity rules (NIST guidelines).
- **Storage**: Argon2id / Bcrypt.

---

## Authorization (AuthZ)

### 1. Role-Based Access Control (RBAC)
- **Rule**: Assign permissions to Roles, not Users.
- **Example**: `User` -> `Role:Editor` -> `Permission:EditPost`.

### 2. Resource Ownership
- **Rule**: Check if user owns the resource.
- **Check**: `if ($post->user_id !== $currentUser->id) deny();`

### 3. Least Privilege
- **Rule**: Default to Deny. Grant only necessary permissions.

---

## Enforcement

- **Middleware**: Auth checks on every route.
- **Tests**: Security tests must cover bypass attempts.
- **Audit**: Log all failed auth attempts.

---

**Related Rules**:
- `@sec:baseline` - Baseline security
- `@sec:privacy` - Data protection
