# Palette's Journal

## 2025-05-15 - [Accessible Mobile Navigation]
**Learning:** Icon-only buttons without ARIA labels and focus indicators are a common accessibility blocker. Using `aria-expanded` and `aria-controls` provides essential state information to screen readers.
**Action:** Always ensure interactive elements have accessible names and visible focus states. Toggling ARIA attributes via JS is necessary to maintain sync between visual and semantic states.
