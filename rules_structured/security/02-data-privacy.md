---
category: security
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@sec:privacy"
source: "Consolidated from RULE 42 (DPS-S)"
---

# Data Privacy & Protection

**Respect User Data. GDPR/CCPA Compliance by Default.**

---

## Core Principle

WHEN handling user data THEN minimize, encrypt, and audit.

IF data is PII (Personally Identifiable Information) THEN treat as toxic.

---

## Data Classification

### 1. Public
- **Examples**: Product names, public blog posts.
- **Protection**: None.

### 2. Internal
- **Examples**: Internal IDs, non-sensitive configs.
- **Protection**: Auth required.

### 3. Confidential (PII)
- **Examples**: Name, Email, IP, Phone.
- **Protection**: Encrypted at rest. Access logging.

### 4. Restricted (PCI/PHI)
- **Examples**: Credit Cards, Health Data, Passwords.
- **Protection**: Tokenization. Strict access controls. Never in logs.

---

## Handling Rules

### 1. Data Minimization
- **Rule**: Collect only what is needed.
- **Rule**: Delete when no longer needed (Retention Policy).

### 2. Right to be Forgotten
- **Rule**: System must support hard deletion of user data.
- **Mechanism**: `User::delete()` must cascade or anonymize.

### 3. Encryption
- **At Rest**: DB encryption (AES-256).
- **In Transit**: TLS 1.2+ required.

---

## Enforcement

- **Code Review**: Flag unnecessary PII collection.
- **Database**: Audit columns for PII.
- **Logs**: Automated scanning for leaked PII.

---

**Related Rules**:
- `@sec:baseline` - Security baseline
- `@sec:secrets` - Secrets management
