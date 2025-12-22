# AI Interface Design

**Category**: Design
**Priority**: P2 (Important for AI Apps)
**Shorthand**: `@design:ai`

---

## 🎯 Purpose

Standardize how AI agents, chat interfaces, and generative elements interact with users. Ensure transparency, trust, and usability in AI-driven experiences.

---

## 📋 Rules

### Rule 1: Visibility of System Status (AI Edition)

**Bad** ❌:
- AI freezes with no feedback while thinking.
- Sudden text appearance after long delay.
- "Processing..." with no details.

**Good** ✅:
- **Thinking State**: Show active "thinking" animation (pulsing, typewriter).
- **Process Steps**: Display "Searching database...", "Analyzing results...", "Generating response...".
- **Streaming**: Stream text token-by-token handling to reduce perceived latency.

### Rule 2: Distinguish AI vs Human Content

**Bad** ❌:
- AI messages look identical to user messages.
- Generated images presented without labels.

**Good** ✅:
- **Visual Distinction**: Use different background colors, icons, or borders for AI content.
- **Iconography**: Use ✨ (sparkles) or 🤖 (bot) icons for AI actions.
- **Labeling**: Explicitly label "AI Generated" for artifacts.

### Rule 3: Human-in-the-Loop Feedback

**Bad** ❌:
- No way to correct AI mistakes.
- One-way conversation.

**Good** ✅:
- **Feedback Mechanism**: Thumbs up/down, "Regenerate", or "Edit" buttons on responses.
- **Correction Flow**: Allow users to refine constraints ("Make it shorter", "Use Python instead").
- **Citation**: Link sources for factual claims.

### Rule 4: Generative UI Interactions

**Bad** ❌:
- AI outputs raw JSON/Code when a UI widget is better.

**Good** ✅:
- **Adaptive UI**: Render AI output as interactive components (Charts, Tables, Maps) when appropriate.
- **Action Buttons**: Provide "Apply Changes", "Copy", "Run Code" actions near outputs.

---

## 🎨 UI Patterns

### Chat Interface
- **User**: Right-aligned, primary color bubble.
- **AI**: Left-aligned, neutral/surface color bubble.
- **Typing Indicator**: 3-dot pulse animation.

### Feedback
- **Positive**: Green/Success accent.
- **Negative**: Red/Warning accent + text input for reason.

---

## ✅ Checklist

- [ ] Is there a clear "Thinking" state?
- [ ] Is AI content visually distinct?
- [ ] Can the user stop/interrupt generation?
- [ ] Is there a feedback mechanism?
- [ ] Are synthesized facts cited?

---

**Status**: ✅ Active
**Version**: 1.0.0
**Date**: 2025-12-08
