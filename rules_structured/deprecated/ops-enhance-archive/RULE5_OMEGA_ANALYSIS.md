# RULE 5 (OMEGA EDITION) — Analysis & Integration Report

**Generated:** 2025-01-27  
**Scope:** Comparison of RULE 5 (OMEGA EDITION) against existing `codequality.mdc` and `clean-code.mdc`

---

## Executive Summary

**RULE 5 (OMEGA EDITION)** represents a **zero-trust, fully automated, multi-dimensional code quality governance system** that significantly expands and hardens the existing rules. While `codequality.mdc` and `clean-code.mdc` provide foundational guidelines, RULE 5 adds:

- **AST-based structural validation** (complexity, nesting, dead code)
- **Behavioral quality gates** (side effects, state mutation, architectural purity)
- **AI-powered semantic analysis** (naming intent, domain vocabulary)
- **Cross-language consistency enforcement**
- **Hard execution blocks** (zero-trust enforcement)
- **Automated nightly drift detection**

---

## 1. Coverage Comparison Matrix

| Dimension | `codequality.mdc` | `clean-code.mdc` | **RULE 5 (OMEGA)** | Status |
|-----------|-------------------|------------------|-------------------|--------|
| **Formatting** | ✅ Single formatter, version pinning | ❌ | ✅ + Zero-trust enforcement | **Enhanced** |
| **Type Safety** | ✅ Type checking (TS/PHPStan/mypy) | ❌ | ✅ + Mandatory strict types, return types, visibility | **Enhanced** |
| **Complexity** | ❌ | ❌ | ✅ Max cyclomatic, cognitive, nesting depth=3 | **NEW** |
| **Naming** | ❌ | ✅ Meaningful names, avoid conflicts | ✅ + Semantic validator, domain vocabulary, banned patterns | **Enhanced** |
| **Structure** | ❌ | ✅ Single responsibility, DRY | ✅ + AST validation, max function/class length | **Enhanced** |
| **Testing** | ✅ ≥80% coverage, ≥90% changed lines | ✅ Test before bugs | ✅ + Testability analyzer, forced DI, mockability | **Enhanced** |
| **Maintainability** | ❌ | ✅ Refactor continuously | ✅ + Maintainability index, duplication <5%, churn monitoring | **NEW** |
| **Architecture** | ❌ | ✅ Cross-cutting separation | ✅ + Service purity, repository pattern, no business logic in controllers | **Enhanced** |
| **Behavioral Quality** | ❌ | ❌ | ✅ No side effects, no hidden I/O, no global mutation | **NEW** |
| **Predictability** | ❌ | ❌ | ✅ Deterministic behavior, consistent error model, logging format | **NEW** |
| **Cross-Language** | ❌ | ❌ | ✅ Identical domain vocabulary, error taxonomy, module boundaries | **NEW** |
| **Hard Bans** | ✅ Secrets, large binaries | ✅ Magic numbers (via constants) | ✅ + 15+ banned patterns (commented code, debug leftovers, god classes, etc.) | **Enhanced** |
| **Enforcement** | ✅ CI gates (lint, type, test, security) | ❌ | ✅ Pre-commit + PR + Nightly + Zero-trust execution blocks | **Enhanced** |
| **Documentation** | ✅ README/CHANGELOG, public APIs | ✅ Comments explain WHY | ✅ + All public methods docblocks, domain events, invariants | **Enhanced** |

---

## 2. Gap Analysis

### 2.1 Missing from Existing Rules (Now Covered by RULE 5)

#### **Structural Quality (AST-Based)**
- ❌ **Max function/class length** — Not specified
- ❌ **Cyclomatic complexity limits** — Not specified
- ❌ **Cognitive complexity limits** — Not specified
- ❌ **Max nesting depth = 3** — Not specified
- ❌ **Dead code detection** — Not specified
- ❌ **Unreachable branch detection** — Not specified

#### **Behavioral Quality**
- ❌ **Side effect validation** — Not specified
- ❌ **Hidden I/O detection** — Not specified
- ❌ **Global state mutation rules** — Not specified
- ❌ **Silent exception swallowing** — Not specified
- ❌ **State mutation in read-only functions** — Not specified
- ❌ **Blocking calls in async paths** — Not specified
- ❌ **Service-layer purity** — Not specified
- ❌ **Repository pattern requirement** — Not specified

#### **Predictability & Consistency**
- ❌ **Deterministic behavior requirement** — Not specified
- ❌ **Consistent error model** — Not specified
- ❌ **Consistent logging format** — Not specified
- ❌ **Cross-language domain vocabulary** — Not specified

#### **Testability Quality**
- ❌ **Testability analyzer** — Not specified
- ❌ **Forced dependency injection** — Not specified
- ❌ **Mockability requirements** — Not specified

#### **Maintainability Quality**
- ❌ **Maintainability index threshold** — Not specified
- ❌ **Code duplication < 5%** — Not specified
- ❌ **File/method churn monitoring** — Not specified

#### **Enforcement Mechanisms**
- ❌ **Pre-commit hooks** — Not specified
- ❌ **Nightly drift detection** — Not specified
- ❌ **Zero-trust execution blocks** — Not specified
- ❌ **AI semantic reviewers** — Not specified

---

### 2.2 Already Covered (Alignment Check)

| Existing Rule | RULE 5 Equivalent | Alignment |
|--------------|-------------------|-----------|
| `codequality.mdc`: Single formatter | RULE 5: Formatting enforcement | ✅ **Aligned** |
| `codequality.mdc`: Type checking | RULE 5: Mandatory strict types | ✅ **Aligned** (RULE 5 stricter) |
| `codequality.mdc`: ≥80% coverage | RULE 5: Testability quality | ✅ **Aligned** (RULE 5 adds testability analyzer) |
| `codequality.mdc`: CI gates | RULE 5: Multi-layer enforcement | ✅ **Aligned** (RULE 5 adds pre-commit + nightly) |
| `codequality.mdc`: Parameterize queries | RULE 5: No inline SQL | ✅ **Aligned** |
| `clean-code.mdc`: Meaningful names | RULE 5: Semantic naming + domain vocabulary | ✅ **Aligned** (RULE 5 adds AI validation) |
| `clean-code.mdc`: Single responsibility | RULE 5: Structural + behavioral quality | ✅ **Aligned** (RULE 5 adds AST validation) |
| `clean-code.mdc`: DRY | RULE 5: Duplication < 5% | ✅ **Aligned** (RULE 5 quantifies) |
| `clean-code.mdc`: Cross-cutting separation | RULE 5: Service purity + repository pattern | ✅ **Aligned** (RULE 5 enforces architecture) |
| `clean-code.mdc`: Constants over magic numbers | RULE 5: No magic numbers | ✅ **Aligned** |

---

## 3. Enhanced Requirements (RULE 5 Adds)

### 3.1 Zero-Trust Enforcement
**Existing:** CI blocks merges on failures  
**RULE 5:** Code execution disabled at editor level, local dev server refuses to start, AI refuses generation

### 3.2 Multi-Dimensional Quality Model
**Existing:** Separate concerns (formatting, testing, security)  
**RULE 5:** Six simultaneous gates (structural, behavioral, readability, predictability, testability, maintainability) — all must pass

### 3.3 Semantic Naming Governance
**Existing:** "Meaningful names" (subjective)  
**RULE 5:** AI-based semantic validator, banned ambiguous names (Util, Helper, Manager, Processor), domain vocabulary enforcement

### 3.4 Cross-Language Consistency
**Existing:** Not specified  
**RULE 5:** Identical domain vocabulary, error taxonomy, logging schema, module boundaries across PHP/Python/JS/Go/etc.

### 3.5 Hard Prohibited Code List
**Existing:** Secrets, large binaries  
**RULE 5:** 15+ banned patterns (commented code, debug leftovers, god classes, nested conditionals >3, mutable global state, etc.)

### 3.6 Nightly Drift Detection
**Existing:** Not specified  
**RULE 5:** Quality drift, maintainability regression, naming regression, boundary drift, duplicate logic creep

---

## 4. Conflicts & Clarifications Needed

### 4.1 Exception Handling
**RULE 5:** Exceptions allowed only for migrations/bootstrap/framework code, with `exception.json`, ≤14 days expiry, approval required  
**Existing:** No exception mechanism specified  
**Resolution:** RULE 5 adds governance; no conflict, but needs implementation

### 4.2 Test Coverage Thresholds
**Existing:** ≥80% project, ≥90% changed lines  
**RULE 5:** Not explicitly stated (implied via testability quality)  
**Resolution:** Keep existing thresholds; RULE 5 adds testability analyzer on top

### 4.3 Maintainability Index
**RULE 5:** "Maintainability index above threshold" (not specified)  
**Existing:** Not specified  
**Resolution:** Need to define threshold (e.g., ≥70 Halstead Maintainability Index)

### 4.4 Complexity Limits
**RULE 5:** "Max function length (configurable)", "Max cyclomatic complexity" (not specified)  
**Existing:** Not specified  
**Resolution:** Need to define defaults (e.g., max function length=50 lines, cyclomatic complexity=10)

---

## 5. Integration Recommendations

### 5.1 Merge Strategy

**Option A: Superset Integration (Recommended)**
- Keep `codequality.mdc` and `clean-code.mdc` as foundational guidelines
- Create new `codequality-omega.mdc` with RULE 5 enhancements
- Reference foundational rules from omega rule
- Enables gradual adoption

**Option B: Full Replacement**
- Replace `codequality.mdc` and `clean-code.mdc` with consolidated `codequality-omega.mdc`
- Risk: May be too strict for existing codebase
- Benefit: Single source of truth

**Option C: Layered Approach**
- `codequality.mdc` = Baseline (current)
- `clean-code.mdc` = Principles (current)
- `codequality-omega.mdc` = Enforcement layer (RULE 5)
- Tools reference all three, apply strictest rule

### 5.2 Implementation Phases

#### **Phase 1: Foundation (Weeks 1-2)**
- [ ] Define complexity thresholds (function length, cyclomatic, cognitive)
- [ ] Define maintainability index threshold
- [ ] Set up pre-commit hooks (format, lint, type-check)
- [ ] Configure AST analyzers (ESLint complexity rules, PHPStan, etc.)

#### **Phase 2: CI Integration (Weeks 3-4)**
- [ ] Add AST structural scan to PR pipeline
- [ ] Add semantic naming scan (AI-based or regex-based)
- [ ] Add duplication scan (SonarQube or similar)
- [ ] Add maintainability index scan
- [ ] Add testability analyzer

#### **Phase 3: Zero-Trust Enforcement (Weeks 5-6)**
- [ ] Implement editor-level quality violation detection
- [ ] Implement local dev server quality gate
- [ ] Implement AI generation blocker
- [ ] Create exception.json mechanism

#### **Phase 4: Nightly Monitoring (Weeks 7-8)**
- [ ] Set up nightly quality drift detection
- [ ] Set up maintainability regression alerts
- [ ] Set up naming regression alerts
- [ ] Set up duplicate logic creep detection

#### **Phase 5: Cross-Language Consistency (Weeks 9-10)**
- [ ] Define domain vocabulary glossary
- [ ] Define error taxonomy
- [ ] Define logging schema
- [ ] Create consistency validator

---

## 6. Example: RULE 5 Violation Analysis

### Example Code (from RULE 5)
```php
function processOrder($order) {
   $total = 0;
   foreach ($order->items as $item) {
      $total += $item->price * $item->qty;
   }
   return $total;
}
```

### Violations Under Existing Rules
- ❌ `codequality.mdc`: Missing type declarations
- ❌ `clean-code.mdc`: Unclear naming (`processOrder` doesn't reveal it calculates total)
- ❌ `clean-code.mdc`: Magic numbers (implicit 0, 1)

### Additional Violations Under RULE 5
- ❌ **Structural:** No docblock, no return type, no parameter types
- ❌ **Behavioral:** Mixing iteration + calculation (not single responsibility)
- ❌ **Readability:** Name doesn't match behavior (processes vs calculates)
- ❌ **Predictability:** No error handling, unclear failure behavior
- ❌ **Testability:** Tightly coupled to `$order` structure, not mockable
- ❌ **Maintainability:** No domain vocabulary, no invariant checking

### RULE 5 Compliant Version
```php
/**
 * Calculates the total price of all items in an order.
 *
 * @param Order $order The order containing items to calculate
 * @return float The total price in the order's currency
 * @throws InvalidOrderException If order has no items or invalid item prices
 */
public function calculateOrderTotal(Order $order): float
{
    if ($order->getItems()->isEmpty()) {
        throw new InvalidOrderException('Order must contain at least one item');
    }

    $total = 0.0;
    foreach ($order->getItems() as $item) {
        $itemTotal = $this->calculateItemTotal($item);
        $total += $itemTotal;
    }

    return $total;
}

/**
 * Calculates the total price for a single order item.
 *
 * @param OrderItem $item The order item
 * @return float The item total (price × quantity)
 * @throws InvalidItemException If price or quantity is invalid
 */
private function calculateItemTotal(OrderItem $item): float
{
    $price = $item->getPrice();
    $quantity = $item->getQuantity();

    if ($price < 0 || $quantity < 1) {
        throw new InvalidItemException('Item price and quantity must be positive');
    }

    return $price * $quantity;
}
```

**Improvements:**
- ✅ Type declarations (parameter, return)
- ✅ Docblocks (public method)
- ✅ Single responsibility (separated item calculation)
- ✅ Domain vocabulary (`Order`, `OrderItem`, `calculateOrderTotal`)
- ✅ Error handling (exceptions)
- ✅ Testable (can mock `Order` and `OrderItem`)
- ✅ Invariant checking (empty order, negative prices)

---

## 7. Tooling Requirements

### 7.1 AST Analyzers (Required)
- **JavaScript/TypeScript:** ESLint (complexity rules), TypeScript compiler
- **PHP:** PHPStan (level 9), Psalm, PHP_CodeSniffer
- **Python:** Pylint, mypy, Radon (complexity)
- **Go:** golangci-lint, go vet
- **Java:** Checkstyle, PMD, SpotBugs

### 7.2 Semantic Analyzers (Required)
- **Naming Intent:** Custom AI-based validator (OpenAI/Claude API) or regex patterns
- **Domain Vocabulary:** Glossary-based validator
- **Cross-Language Consistency:** Custom tool comparing vocabularies across languages

### 7.3 Testability Analyzers (Required)
- **Dependency Injection Detection:** AST-based analysis
- **Mockability Check:** Static analysis of function dependencies
- **Hidden I/O Detection:** AST-based analysis of file/network calls

### 7.4 Maintainability Analyzers (Required)
- **Duplication:** SonarQube, jscpd, PMD CPD
- **Maintainability Index:** Radon (Python), CodeClimate, SonarQube
- **Churn Monitoring:** Git-based analysis (git log, git blame)

### 7.5 Behavioral Analyzers (Required)
- **Side Effect Detection:** AST-based analysis
- **Global State Mutation:** Static analysis
- **Silent Exception Swallowing:** AST-based analysis (empty catch blocks)

---

## 8. Configuration Template

### `ops/enhance/codequality-omega.json`
```json
{
  "structural": {
    "maxFunctionLength": 50,
    "maxClassLength": 300,
    "maxCyclomaticComplexity": 10,
    "maxCognitiveComplexity": 15,
    "maxNestingDepth": 3,
    "requireStrictTypes": true,
    "requireReturnTypes": true,
    "requireParameterTypes": true,
    "requireVisibilityKeywords": true
  },
  "behavioral": {
    "allowGlobalStateMutation": false,
    "requireServiceLayerPurity": true,
    "requireRepositoryPattern": true,
    "forbidBusinessLogicInControllers": true,
    "forbidSilentExceptionSwallowing": true
  },
  "readability": {
    "requirePublicMethodDocblocks": true,
    "requireDomainVocabulary": true,
    "bannedNames": ["Util", "Helper", "Data", "Foo", "Tmp", "X1", "Manager", "Processor", "Handler"]
  },
  "predictability": {
    "requireDeterministicBehavior": true,
    "requireConsistentErrorModel": true,
    "requireConsistentLoggingFormat": true
  },
  "testability": {
    "requireDependencyInjection": true,
    "requireMockability": true,
    "forbidHiddenDatabaseCalls": true
  },
  "maintainability": {
    "maxDuplicationPercent": 5,
    "minMaintainabilityIndex": 70,
    "monitorFileChurn": true,
    "monitorMethodChurn": true
  },
  "crossLanguage": {
    "requireIdenticalDomainVocabulary": true,
    "requireIdenticalErrorTaxonomy": true,
    "requireIdenticalLoggingSchema": true
  },
  "hardBans": {
    "commentedCode": true,
    "debugLeftovers": true,
    "godClasses": true,
    "copyPasteLogic": true,
    "inlineSQL": true,
    "nestedConditionalsBeyond3": true,
    "mutableGlobalState": true,
    "directDBAccessFromControllers": true,
    "dynamicEvaluation": true
  },
  "enforcement": {
    "preCommit": true,
    "prPipeline": true,
    "nightly": true,
    "zeroTrustExecutionBlocks": true
  },
  "exceptions": {
    "allowedFor": ["migrations", "bootstrap", "framework"],
    "maxExpiryDays": 14,
    "requireApproval": ["principal_engineer", "qa_lead"]
  }
}
```

---

## 9. Conclusion

### 9.1 Alignment Status
✅ **RULE 5 (OMEGA EDITION) is fully compatible with existing rules** — it enhances and hardens them rather than conflicting.

### 9.2 Key Additions
- **AST-based structural validation** (complexity, nesting, dead code)
- **Behavioral quality gates** (side effects, state mutation, architectural purity)
- **AI-powered semantic analysis** (naming intent, domain vocabulary)
- **Cross-language consistency enforcement**
- **Zero-trust execution blocks**
- **Nightly drift detection**

### 9.3 Recommended Action
1. **Create `codequality-omega.mdc`** with RULE 5 content
2. **Reference existing rules** from omega rule (foundational principles)
3. **Implement tooling** in phases (see Section 5.2)
4. **Configure thresholds** (see Section 8)
5. **Gradual adoption** (start with new code, then legacy)

### 9.4 Risk Assessment
- **High Risk:** Zero-trust execution blocks may be too strict for existing codebase
- **Mitigation:** Start with warnings, then enforce on new code only, then legacy
- **Medium Risk:** Cross-language consistency requires significant coordination
- **Mitigation:** Start with single language, expand gradually

---

**Next Steps:**
1. Review and approve this analysis
2. Create `codequality-omega.mdc` file
3. Define configuration thresholds
4. Set up Phase 1 tooling (pre-commit hooks, AST analyzers)
5. Create exception.json mechanism

