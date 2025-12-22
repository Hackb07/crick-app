# 🛑 PREFLIGHT CHECKLIST

**DO NOT WRITE CODE UNTIL YOU HAVE CHECKED THESE BOXES.**

This checklist is the "Correction Action" for past mistakes. It forces a pause before execution to ensure rules are followed.

## 1. 🏗️ Architecture & Structure
- [ ] **MVC Check**: Am I putting logic in a View file? -> STOP. Creation a Controller.
- [ ] **File Size**: Will this file exceed 300 lines? -> STOP. Modularize immediately (CSS to assets, JS to assets).
- [ ] **Path Verification**: Am I using `assetUrl()` correctly? Do the files exist at that path?

## 2. 🎨 Design & Identity
- [ ] **Context Check**: Is this page **Public** or **Admin**?
    - **Public** -> Use `premium-design.css` (Green/Gold).
    - **Admin** -> Use `admin-pwa.css` (Blue/Gray).
- [ ] **Consistency**: Does my design clash with the Sidebar or Header?
- [ ] **Mobile First**: Will this layout break on a 375px screen?

## 3. 🛡️ Logic & Safety
- [ ] **Method Verification**: Am I calling a function/method? -> **Verify it spans existence** in the target class/file.
- [ ] **Variable Scoping**: Am I redeclaring a variable? -> Check global scope.
- [ ] **Null Safety**: Am I accessing an array index or object property? -> Add `isset()` or `??` fallback.

## 4. 📝 Rule Compliance
- [ ] **Read MISTAKES_LOG.md**: Have I reviewed the active mistakes list?
- [ ] **Read Project Rules**: Have I checked `@rules_structured` for this specific domain (e.g., `@design:forms`)?

---

**Signed off by**: ____________________ (Mental Check)
