---
category: architecture
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@arch:structure"
source: "Project Standards & Execution Handbook"
---

# Project Structure Standards

**Folder Placement Defines Meaning. Structure is Architecture.**

---

## Core Principle

WHEN organizing code THEN follow the standard root and module structure.

IF a file is outside `/src` (except config/infra) THEN it is misplaced.

---

## Root Structure

```text
/
├── src/          # Source code (feature logic)
├── tests/        # Test suites (mirrors src)
├── docs/         # Documentation (ADRs, API specs)
├── scripts/      # Automation & utility scripts
├── infra/        # Infrastructure (Docker, Terraform)
├── public/       # Static assets (if applicable)
└── config/       # Environment loader & validator
```

## Source Directory (`/src`)

```text
/src
├── api/          # Routing & Controllers (Entry points)
├── modules/      # Feature logic (Domain/App/Infra)
├── services/     # Shared/Reusable services
├── models/       # Database schemas/ORM models
├── lib/          # Utilities & Helpers
└── config/       # App configuration
```

## Module Structure (DDD Lite)

For feature-based modules in `/src/modules/<feature>`:

```text
/src/modules/user/
├── domain/       # Entities, Value Objects, Domain Services
├── app/          # Use Cases, Command Handlers
├── infra/        # Repositories, API Clients
└── index.ts      # Public API of the module
```

### Rules
1.  **No Source Outside `/src`**: All application logic lives in `src`.
2.  **No Logic in `/api`**: API layer is for routing and request handling only.
3.  **No Random Folders**: Every folder must map to a standard layer.

---

## File & Folder Naming

**Rule**: Use `kebab-case` for all files and folders.

**Examples**:
- ✅ `user-controller.ts`
- ✅ `payment-service.php`
- ✅ `/user-management/`
- ❌ `UserController.ts`
- ❌ `paymentService.php`

---

## Test Mirroring

**Rule**: Test structure MUST mirror source structure.

**Example**:
- Source: `src/modules/user/domain/user.ts`
- Test: `tests/modules/user/domain/user.test.ts`

---

## Enforcement

- **CI**: Linter checks for file naming (`kebab-case`).
- **Review**: Reject PRs with random folder structures.
- **Automation**: `repo-indexer.js` validates structure.

---

**Related Rules**:
- `@arch:domain` - Domain modeling rules
- `@quality:naming` - Code naming conventions
