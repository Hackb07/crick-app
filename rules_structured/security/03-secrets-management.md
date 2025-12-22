---
category: security
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@sec:secrets"
source: "Consolidated from RULE 43 (SKM-S) & RULE 9 (CONF-S)"
---

# Secrets Management

**No Secrets in Code. No Secrets in Git. Rotation is Mandatory.**

---

## Core Principle

WHEN handling secrets THEN inject them at runtime.

IF secret is found in code THEN revoke and rotate immediately.

---

## Storage Rules

### 1. Environment Variables
- **Rule**: Use `$_ENV` or `process.env`.
- **File**: `.env` (local only), `.env.example` (template).
- **Git**: Add `.env` to `.gitignore`.

### 2. Vault Integration
- **Rule**: Production secrets live in Vault (AWS Secrets Manager, HashiCorp Vault).
- **Access**: App fetches secrets at startup.

### 3. CI/CD Secrets
- **Rule**: Use GitHub Actions Secrets / GitLab Variables.
- **Log Masking**: Ensure CI masks secrets in logs.

---

## Environment Validation Pattern

**Rule**: Validate `process.env` / `$_ENV` at startup.

**Pattern**:
1.  **Schema**: Define expected env vars and types (e.g., using Zod/Joi).
2.  **Config Object**: Parse env vars into a typed config object.
3.  **Fail Fast**: If validation fails, crash immediately with clear error.

**Example**:
```typescript
// config/env.ts
const envSchema = z.object({
  DATABASE_URL: z.string().url(),
  API_KEY: z.string().min(1),
  PORT: z.coerce.number().default(3000),
});

export const config = envSchema.parse(process.env);
```

---

## Rotation Policy

### 1. Automated Rotation
- **Target**: DB Credentials, API Keys.
- **Frequency**: Every 90 days.

### 2. Emergency Rotation
- **Trigger**: Accidental commit, employee exit, breach.
- **Action**: Revoke old key -> Deploy new key -> Restart services.

---

## Anti-Patterns

- ❌ **Hardcoding**: `const API_KEY = "xyz"`
- ❌ **Committing .env**: Never.
- ❌ **Logging Secrets**: `log("Token: " + token)`
- ❌ **Default Passwords**: Change them.

---

## Enforcement

- **Pre-Commit**: `gitleaks` scan.
- **CI**: Secret scanning pipeline.
- **Runtime**: Crash if required secrets are missing.

---

**Related Rules**:
- `@sec:baseline` - Security baseline
- `@ops:hardening` - Operational security
