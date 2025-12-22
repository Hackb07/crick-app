# Form Design Patterns

## 1. Form Structure
- **Single Column**: Preferred for most forms (easier eye tracking).
- **Logical Grouping**: Use fieldsets and visual separation for related fields.
- **Progressive Disclosure**: Show advanced options only when needed.
- **Field Order**: Most important/common fields first.

## 2. Input Design
- **Label Position**: Top-aligned labels (fastest completion time).
- **Placeholder Text**: Use for examples, NOT as labels (accessibility issue).
- **Input Size**: Match input width to expected content length.
- **Required Fields**: Mark clearly, use `*` or "(required)" text.

## 3. Validation & Error Handling
- **Inline Validation**: Validate on blur, show errors immediately.
- **Error Messages**: Specific and actionable ("Email must include @" not "Invalid").
- **Success States**: Confirm valid input with checkmark or green border.
- **Error Position**: Show errors directly below the field, not in a summary at top.

## 4. Accessibility
- **Labels**: Every input MUST have an associated `<label>` with `for` attribute.
- **Error Announcements**: Use `aria-describedby` to link errors to inputs.
- **Focus Management**: Move focus to first error on submit.
- **Keyboard Navigation**: Tab order must be logical and complete.

## 5. Mobile Optimization
- **Input Types**: Use correct HTML5 types (`email`, `tel`, `number`) for proper keyboards.
- **Touch Targets**: Minimum 44x44px for all interactive elements.
- **Autofill**: Support browser autofill with proper `autocomplete` attributes.
- **Zoom Prevention**: Use `font-size: 16px` minimum to prevent iOS zoom on focus.

## 6. Multi-Step Forms
- **Progress Indicator**: Show current step and total steps.
- **Save Progress**: Allow users to save and return later.
- **Back Navigation**: Always allow going back without losing data.
- **Review Step**: Final review before submission for complex forms.
