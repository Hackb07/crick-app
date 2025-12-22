---
category: architecture
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@arch:boundary"
source: "Consolidated from RULE 31 (CDBE-S)"
---

# Boundary Enforcement

**No Circular Dependencies. No Cross-Layer Contamination. Perfect Architecture.**

---

## Core Principle

WHEN defining module boundaries THEN enforce them at all levels: static, runtime, data, events, workflows.

IF circular dependency detected THEN block immediately.

---

## 7-Dimensional Dependency Control

### 1. Static Dependency Graph

**Enforced**: imports, includes, use statements

**Rules**:
- WHEN importing THEN no circular imports
- WHEN layering THEN no upward edges (lower layers cannot import upper layers)
- WHEN crossing domains THEN must be whitelisted
- NO domain → infrastructure imports
- NO UI → repository imports
- NO cross-layer contamination

**Validation**: Static analysis in CI

### 2. Runtime Execution Graph

**Enforced**: dynamic imports, DI, reflection

**Rules**:
- WHEN runtime dependency detected THEN validate against declared boundaries
- IF runtime cycle detected THEN fail build
- IF forbidden edge detected THEN block deployment
- Log architecture risk for review

**Validation**: Runtime instrumentation

### 3. Data Ownership Graph

**Enforced**: read/mutate permissions

**Rules**:
- WHEN accessing data THEN respect ownership boundaries
- NO unauthorized data mutation
- NO cross-service writes (unless explicitly allowed)
- NO hidden read flows
- NO schema misuse
- NO side-channel data passing

**Validation**: Data lineage tracking

### 4. Event Lineage Graph

**Enforced**: publish/subscribe flows

**Rules**:
- WHEN publishing event THEN follow producer → consumer direction
- NO cyclic event propagation
- NO bidirectional event streams
- NO illegal topic sharing
- NO cross-bounded-context event leaks

**Validation**: Event flow analysis

### 5. Workflow Dependency Graph

**Enforced**: multi-service process couplings

**Rules**:
- WHEN orchestrating workflow THEN no circular workflows
- NO uncontrolled compensating loops
- NO multi-step external dependency cycles
- NO workflow deadlocks
- NO out-of-order event flows
- NO temporal coupling loops

**Validation**: Workflow analysis

### 6. Version Dependency Graph

**Enforced**: API & schema versions

**Rules**:
- WHEN depending on API THEN no backward-incompatible dependencies
- NO mixed major versions
- NO accidental downgrades
- NO ABI drift
- NO schema misalignment
- NO outdated event-contract consumption

**Validation**: Version compatibility matrix

### 7. Cross-Repository Ecosystem Graph

**Enforced**: multi-service dependency mesh

**Rules**:
- WHEN adding cross-repo dependency THEN scan ALL edges
- IF any repo forms cycle with another THEN freeze ecosystem update
- Enforce system-wide acyclic structure

**Validation**: Cross-repo dependency scanner

---

## Zero-Drift Architecture Model

### Required Files

**Per module**:
- `/docs/architecture/layers.json` - Layer definitions
- `/docs/data/entities.json` - Data ownership
- `/docs/events/contracts.json` - Event contracts
- `/docs/workflows/<workflow>.json` - Workflow definitions
- `/docs/versions/api/<service>.json` - API versions
- `/docs/versions/domain/<module>.json` - Domain versions

### Enforcement

IF any doc contradicts:
- Real code
- Runtime call patterns
- Event lineage
- Schema versions
- Service dependencies

THEN:
- CI blocks immediately
- Deployment blocked
- P1 ticket created
- Architecture team notified

---

## Full Lifecycle Enforcement

### Pre-Commit
- Static graph analysis
- Semantic analyzer
- Quick cycle detection

### PR CI
- Static graph validation
- Runtime graph analysis
- Workflow graph validation
- Event graph validation
- Data lineage check
- Version matrix validation
- Semantic analysis
- Cross-repo analysis

**Any violation → PR blocked**

### Nightly
- Full-system drift scan
- All 7 dimensions validated
- Health score recalculation
- Drift report generated

### Deployment
- Runtime hooks validation
- Event lineage validation
- Version compatibility check
- Cross-repo integration scan

**Any failure → deployment blocked**

---

## Latent Dependency Detection

### Hidden Dependencies Detected

WHEN analyzing THEN detect:
- Shared cache misuse
- Shared log parsing
- Shared config dependency
- Shared DB tables
- Event-side dependencies
- Analytics/event taps
- Backdoor test utilities
- String-level dependency references

### Enforcement

IF module depends on another through anything other than declared code THEN:
- Dependency violation flagged
- Build fails
- Must be explicitly declared or removed

---

## Dependency Health Scoring

### Metrics Per Module

**Calculated continuously**:
- **Fan-in**: Number of modules depending on this module
- **Fan-out**: Number of modules this module depends on
- **Coupling coefficient**: Strength of coupling
- **Churn**: Rate of change
- **Complexity**: Cyclomatic complexity
- **Responsiveness**: API latency
- **Stability**: Error rates
- **Dependency risk factor**: Combined risk score

### Thresholds

| Metric | Warning | Critical |
|--------|---------|----------|
| Fan-in | >10 | >20 |
| Fan-out | >5 | >10 |
| Coupling coefficient | >0.7 | >0.9 |
| Churn (changes/week) | >10 | >20 |
| Complexity | >10 | >15 |
| Error rate | >1% | >5% |

### Enforcement

IF score exceeds threshold THEN:
- Auto-create ticket
- Freeze new dependencies
- Require architecture review
- Refactoring plan required

---

## Exception Governance

### Exception Requirements

**Must include**:
- Automatic risk scoring
- Automatic compensating constraint
- Automatic expiry (max 14 days)
- Automatic rollback scheduling
- AI gatekeeper approval (minor exceptions)
- Human + AI joint approval (major exceptions)

### Exception Process

**Minor exceptions** (single boundary violation, low risk):
1. AI gatekeeper reviews
2. Risk score calculated
3. Compensating controls auto-generated
4. Expiry set (7 days)
5. Monitoring enabled

**Major exceptions** (multiple violations, high risk):
1. Human architect reviews
2. AI provides risk analysis
3. Joint approval required
4. Compensating controls mandatory
5. Expiry set (14 days max)
6. Daily review required

### Enforcement

IF exception expires THEN:
- Auto-remove exception
- Re-enforce boundary
- Block CI if violation still exists
- Escalate to architecture team

---

## Boundary Patterns

### Allowed Patterns

**Layered Architecture**:
```
Presentation → Application → Domain → Infrastructure
(each layer can only depend on layers below)
```

**Hexagonal Architecture**:
```
Core Domain (center)
← Ports (interfaces)
← Adapters (implementations)
(dependencies point inward)
```

**Microservices**:
```
Service A → API Gateway → Service B
(no direct service-to-service calls)
```

### Forbidden Patterns

❌ **Circular Dependencies**:
```
Module A → Module B → Module C → Module A
```

❌ **Upward Dependencies**:
```
Infrastructure → Domain
Repository → Controller
```

❌ **Cross-Layer Contamination**:
```
UI → Database (skipping business logic)
```

❌ **Hidden Dependencies**:
```
Module A → Shared Cache ← Module B
(implicit dependency via cache)
```

---

## Anti-Patterns

- ❌ **Circular dependencies** - Any form of dependency cycle
- ❌ **Hidden dependencies** - Dependencies not declared or detected
- ❌ **Architecture drift** - Code diverging from declared architecture
- ❌ **Cross-layer contamination** - Dependencies violating layer boundaries
- ❌ **Unmanaged dependencies** - Dependencies without health scoring
- ❌ **Exception abuse** - Creating exceptions without proper controls
- ❌ **Latent coupling** - Implicit dependencies via shared resources

---

## Metrics

- **Cycle detection rate**: # of cycles detected (target: 0)
- **Drift incidents**: # of architecture drift violations (target: 0)
- **Health score compliance**: % of modules within thresholds (target: 100%)
- **Exception rate**: % of dependencies requiring exceptions (target: <5%)
- **Latent dependency rate**: # of hidden dependencies detected (target: 0)

---

## Enforcement

- Always apply (P1)
- CI blocks on violations
- Deployment blocks on violations
- Nightly drift detection
- Automated health scoring
- Exception governance enforced

---

**Related Rules**:
- `@arch:intent` - AIS defines dependency boundaries
- `@arch:di` - Dependency injection complements enforcement
- `@arch:drift` - Drift detection complements boundary enforcement
- `@quality:standards` - Code quality affects coupling
