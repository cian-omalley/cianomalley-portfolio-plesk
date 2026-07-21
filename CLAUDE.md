# CLAUDE.md — Portfolio Website Development Standards

> This project is a **hand-coded WordPress theme** (`digital-district/`), chosen so the
> site runs on WordPress and works with Plesk's WP Toolkit and free WordPress SEO
> plugins. These standards supersede the earlier Next.js stack for this repository.

## Core Development Commands

- Lint PHP (every file): `find digital-district -name '*.php' -print0 | xargs -0 -n1 php -l`
- Check JS syntax: `node --check digital-district/assets/js/main.js`
- Package the theme for upload: `cd digital-district && zip -r ../digital-district.zip .`
- Local WordPress (optional): run via Plesk WP Toolkit, Local, or `wp-env`.

## Execution Pacing & Alignment Rules

- **Single-Question Constraint:** Limit clarifying questions to exactly ONE per turn when gathering requirements. Avoid presenting multi-part questions or checklists.
- **Anti-Vagueness Directive:** Do not write boilerplate files, stub methods, or placeholder comments like `// TODO: implement mobile drawer`. All templates and functions must be fully functional.
- **Surgical Code Updates:** Do not rewrite whole files to showcase small code modifications. Provide compact, localized search-and-replace updates.

## Front-End & Architecture Standards

- **Platform:** WordPress (classic PHP theme), PHP ≥ 8.1, WordPress ≥ 6.5. No page builder.
- **Security:** Every PHP file starts with `defined( 'ABSPATH' ) || exit;`. Sanitize input, escape output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`). Custom queries use `$wpdb->prepare()`. Forms use nonces.
- **SEO-friendly:** Keep `add_theme_support( 'title-tag' )` and never hard-code `<title>`/meta, so Yoast / The SEO Framework / Rank Math can manage them.
- **Design System:** Strictly follow the tokens declared in `assets/css/tokens.css`. Do not write arbitrary hex colors in components or templates.
- **Smooth Animations:** Use hardware-accelerated transforms (`transform`, `opacity`) for UI motion. Do not trigger layout reflows (animating raw `height`/`width`) unless requested. All motion respects `prefers-reduced-motion` and pointer/touch capability.
- **Accessibility (A11y):** Enforce semantic HTML elements (`<main>`, `<nav>`, `<section>`). Ensure full keyboard navigation, visible focus indicators, and descriptive `aria-label` tags.

## Content Integrity

- Never invent biography, employers, qualifications, clients, or achievements.
- Every project/work/guide/review carries an honest status; derived stats come from real counts.

## Verification & Quality Gates

- **Pre-Planning Stage:** Always outline structural changes and verify includes/template hierarchy before finalizing code modifications.
- **Code Readability & Simplicity:** Keep variable names short and semantic (e.g., use `status` not `totalCalculatedItemStatusValue`). Avoid overly complex chains when a standard loop is clearer.
- **Inline Comments:** Focus comments exclusively on explaining _why_ a responsive breakpoint, performance trade-off, or complex animation curve was chosen — not on restating standard WordPress hooks.
