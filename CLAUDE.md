# CLAUDE.md — Portfolio Website Development Standards

## Core Development Commands

- Run Dev Server: `pnpm dev`
- Production Build: `pnpm build`
- TypeScript Compile Check: `pnpm typecheck` (executes `tsc --noEmit`)
- Lint Check: `pnpm lint`
- Prettier Formatting: `pnpm format` (runs Prettier)

## Execution Pacing & Alignment Rules

- **Single-Question Constraint:** Limit clarifying questions to exactly ONE per turn when gathering requirements. Avoid presenting multi-part questions or checklists.
- **Anti-Vagueness Directive:** Do not write boilerplate files, stub methods, or placeholder comments like `// TODO: implement mobile drawer`. All components must be fully functional.
- **Surgical Code Updates:** Do not rewrite whole files to showcase small code modifications. Provide compact, localized search-and-replace updates.

## Front-End & Architecture Standards

- **Framework & Types:** Next.js (App Router), React 19, and Strict TypeScript.
- **Design System:** Strictly follow the theme configurations, spacing limits, and colors declared in `tailwind.config.js`. Do not write arbitrary hex colors.
- **Smooth Animations:** Rely exclusively on Framer Motion for UI animations. Use hardware-accelerated transforms (`transform`, `scale`, `opacity`). Do not trigger layout reflows (such as animating raw `height` or `width` properties) unless requested.
- **Accessibility (A11y):** Enforce semantic HTML elements (`<main>`, `<nav>`, `<section>`). Ensure full keyboard navigation capability, visible focus indicators, and descriptive `aria-label` tags.

## Verification & Quality Gates

- **Pre-Planning Stage:** Always outline structural changes and verify component imports in a draft workspace before finalizing code modifications.
- **Code Readability & Simplicity:** Keep variable names short and semantic (e.g., use `score` instead of `totalCalculatedUserScoreValue`). Avoid overly complex functional chains when a standard loop is more readable.
- **Inline Comments:** Focus comments exclusively on explaining *why* a responsive breakpoint, performance layout trade-off, or complex animation curve was chosen. Do not write redundant explanations for standard React hooks.
