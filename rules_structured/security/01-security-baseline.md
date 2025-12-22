---
category: security
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@sec:baseline"
source: "Consolidated from RULE 41 (SBS-S) & security-baseline.mdc"
---

# Security Baseline

**Security is Mandatory. 10 Checklists for Every Feature.**

---

## Core Principle

WHEN writing code THEN apply the 10 security checklists.

IF security violation detected THEN block merge immediately.

---

## 1. CSRF Protection

**Rule**: All state-changing requests MUST have anti-CSRF tokens.

**Checklist**:
- [ ] POST/PUT/DELETE requests require token
- [ ] Tokens are cryptographically random
- [ ] Tokens validated server-side
- [ ] Failed validation returns 403 Forbidden

**Example**:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403); die('CSRF violation');
    }
}
```

## 2. XSS Prevention

**Rule**: Escape ALL user output. Context-aware escaping is mandatory.

**Checklist**:
- [ ] HTML output escaped (htmlspecialchars)
- [ ] HTML attributes escaped
- [ ] JavaScript data escaped (json_encode)
- [ ] Content-Security-Policy (CSP) header set

**Example**:
```php
// ✅ Good
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
echo '<script>var data = ' . json_encode($data) . ';</script>';
```

## 3. SQL Injection Prevention

**Rule**: Use prepared statements for ALL database queries.

**Checklist**:
- [ ] No concatenated SQL strings
- [ ] Use PDO/mysqli prepared statements
- [ ] Validate data types before query
- [ ] Use ORM where possible

**Example**:
```php
// ✅ Good
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
```

## 4. Session Security

**Rule**: Sessions must be secure, httpOnly, and strictly managed.

**Checklist**:
- [ ] `cookie_httponly` = 1
- [ ] `cookie_secure` = 1 (HTTPS)
- [ ] `cookie_samesite` = Strict
- [ ] Session ID regenerated on login
- [ ] Session timeout implemented (30m)

## 5. Password Security

**Rule**: Use strong hashing algorithms. Never store plain text.

**Checklist**:
- [ ] Use `password_hash()` with Argon2 or Bcrypt
- [ ] Use `password_verify()`
- [ ] Enforce password complexity
- [ ] No MD5 or SHA1

## 6. Input Validation

**Rule**: Validate ALL input. Whitelist over blacklist.

**Checklist**:
- [ ] Validate type (int, email, etc.)
- [ ] Validate length/range
- [ ] Use whitelist for enums
- [ ] Sanitize file uploads
- [ ] Reject invalid input (fail fast)

## 7. Security Headers

**Rule**: Send security headers with every response.

**Checklist**:
- [ ] `X-Content-Type-Options: nosniff`
- [ ] `X-Frame-Options: DENY`
- [ ] `Strict-Transport-Security` (HSTS)
- [ ] `Content-Security-Policy`

## 8. Auth & Authz

**Rule**: Verify identity and permissions on every protected route.

**Checklist**:
- [ ] Auth check on all protected pages
- [ ] Role check for privileged actions
- [ ] No IDOR (Insecure Direct Object Reference)
- [ ] Fail secure (deny by default)

## 9. File Uploads

**Rule**: Treat uploaded files as hostile.

**Checklist**:
- [ ] Validate MIME type & extension
- [ ] Limit file size
- [ ] Rename file (randomize)
- [ ] Store outside web root

## 10. Error Handling

**Rule**: Never leak sensitive info in errors.

**Checklist**:
- [ ] `display_errors = 0` in production
- [ ] Log errors to file/system
- [ ] Show generic messages to user
- [ ] No stack traces in UI

---

## Enforcement

- **Pre-Commit**: `security-precheck.js` scans for secrets/vulns.
- **CI/CD**: SAST (Semgrep) + DAST (OWASP ZAP) required.
- **Review**: Manual verification of this checklist.

---

**Related Rules**:
- `@arch:nfr` - Security NFRs
- `@quality:errors` - Error handling patterns
