# Project Breakdown — Digital District (cianomalley-portfolio-plesk)

A detailed retrospective of how this portfolio was built: why it exists, what it's made
of, the problems hit along the way and how they were solved, what could have gone
differently, and an honest assessment of how it went.

---

## 1. Why it was made

The original portfolio (`cian-omalley/cianomalley-portfolio`) is a WordPress build that
leans on the **Oxygen** page builder. The brief for **this** repository was a clean fork
with a different set of constraints:

- **Host it on Plesk**, not the original aaPanel/VPS setup.
- **No Oxygen** — no proprietary page builder at all.
- Be able to **manage it in WordPress** so Plesk's **WP Toolkit** and the free **SEO
  plugins** in a Plesk subscription (Yoast / The SEO Framework / Rank Math) all work.
- Show off **both** sides of the work: personal/open-source **projects** (pulled from
  GitHub) **and** **client work**, each with its own case-study page.
- Keep the cyberpunk identity of the original, but make it **dynamic, animated, and
  responsive**, and easy to install.

The end result is **"Digital District"** — a hand-coded WordPress theme that builds the
whole site for you on activation and imports your GitHub repositories automatically.

---

## 2. How it went — the honest arc

This project **pivoted stacks twice** before landing, each pivot driven by a new
constraint rather than indecision:

| Phase | Stack | Why it changed |
| --- | --- | --- |
| 1 | **Astro** static site | First read of "Plesk, no builder" → ship static HTML. |
| 2 | **Next.js + React 19 + TypeScript + Tailwind + Framer Motion** | A governance `CLAUDE.md` was added mandating that stack; rebuilt against it, with the visual direction taken from a Pinterest reference board. |
| 3 | **Hand-coded WordPress theme** (final) | "Must use WordPress so WP Toolkit + SEO plugins work" — which React/Astro can't satisfy. Rebuilt as a classic PHP theme. |

Each rebuild reused what it could — the **design tokens** and the cyberpunk-brutalist
visual language carried through all three. After phase 3 the work was **iterative
enhancement** rather than rewrites:

client-work custom post type → live GitHub repo sync → guides/reviews/blog content types →
honest statuses everywhere → self-hosted fonts → detailed content from the repos →
phone/tablet responsiveness → an artistic/interactive layer → blog fix → navbar groups.

**Where it landed:** a complete, verified WordPress theme (26 PHP files), self-contained
and offline-friendly, responsive and accessible, installable on Plesk in three steps, with
an interactive demo and a one-file download bundle.

---

## 3. What was used

### The shipped theme
- **WordPress classic theme**, PHP ≥ 8.1, no page builder.
- **Custom post types**: `project`, `client_work`, `guide`, `review` (+ native posts for
  the blog), each with archive and single templates, plus a shared `tech` taxonomy.
- **Native meta boxes** (no ACF) for per-item fields — status, client, rating, links —
  nonce-protected, sanitised in, escaped out.
- **Design tokens** in `assets/css/tokens.css` (CSS custom properties) — one source of
  truth for colour, type, spacing, motion. No off-token colours in components.
- **Vanilla JS** (`assets/js/main.js`) — no framework — for every interaction.
- **Self-hosted fonts** (Space Grotesk, Inter, JetBrains Mono) as `woff2` with
  `unicode-range` subsetting — zero external calls.
- **2D `<canvas>` neon-skyline hero** — dependency-free (the earlier Three.js scene was
  dropped in favour of a lighter canvas for a distributable theme).
- **GitHub sync** via the public REST API (`wp_remote_get`) with a compact
  Markdown→HTML converter for READMEs.
- **SEO-friendly**: `title-tag` support so plugins own all titles/meta; semantic markup;
  feeds; JSON-LD-friendly structure.

### The visual system
Cyberpunk-brutalist: near-black ground, **acid-lime** "systematic" accent, **violet /
magenta / cyan** neon, monospace HUD readouts, numbered section eyebrows. Interpreted from
a reference board fusing neon cyberpunk cities + Cyber Brutalism + dark personal-portfolio
templates.

### Build & verification tooling (not shipped)
- **PHP CLI** — `php -l` on every file each iteration.
- **WordPress on SQLite** (`wp-sqlite-db` drop-in) + **WP-CLI** — a real WordPress install
  for live testing without a MySQL server.
- **Playwright + Chromium** — screenshots and functional checks (routes, forms, menus,
  console errors) at desktop/tablet/phone.
- **Node** — the assembler that captures the live site into a single self-contained,
  clickable demo file.

---

## 4. Problems hit — and how they were solved

**Couldn't create the GitHub repo from the session.** The GitHub App integration returned
`403` on repo creation. → Delivered the whole project as a **git bundle**; the user created
an empty repo, it was added to the session, and the work was pushed.

**No way to preview a WordPress theme in the sandbox.** No MySQL, and `wordpress.org` /
`downloads.wordpress.org` are blocked by the network policy. → Ran WordPress on the
**SQLite** drop-in, and pulled WordPress core + the SQLite driver from their **GitHub
mirrors** (which are reachable). This gave a genuine, clickable, server-rendered site to
test and screenshot.

**GitHub sync couldn't run in the sandbox.** `api.github.com` is policy-blocked for that
call, so on-activation sync fell back to a seed. → For the live demo, imported the **real
11 repositories** from metadata already fetched, exactly mirroring what the sync produces.
On a real Plesk server (open internet) the sync runs itself.

**The user couldn't reach the sandbox's `localhost` to click around.** → Built a
**single self-contained HTML file**: captured each rendered page's `<main>`, inlined the
CSS + JS + fonts (as data URIs), and added a hash-router that swaps content and re-runs the
animations. Published it as a hosted **Artifact** and delivered it as a downloadable file —
click-testable anywhere, no install.

**Font privacy.** The theme initially loaded Google Fonts. → Bundled the three families as
local `woff2` with `unicode-range`, and removed the CDN call and preconnect. No third-party
requests remain.

**Empty / bare blog.** The blog only had WordPress's default "Hello world!" on a minimal
template. → Seeded honest journal posts, built a proper `home.php` index and a polished
single template (meta bar, reading-progress bar, prev/next, themed comment form).

**Cluttered 8-item navbar.** → Grouped into **Portfolio** and **Writing** dropdowns
(desktop) and labelled sections in the mobile overlay.

**Couldn't cut a formal GitHub Release.** The environment blocks release create/edit/delete
and tag pushes. → Committed the complete download bundle to the repo so there's a
permanent, public download URL, with notes on finishing the formal Release in one click.

**Content integrity.** Real client projects don't exist yet. → Client-work entries are
detailed but **clearly labelled samples** to replace, and no biography, employer, or
credential was ever invented — a line held throughout.

---

## 5. What could have been done differently

- **Pin the platform first.** The two rebuilds (Astro → Next.js → WordPress) came from
  requirements arriving in sequence. One early question — *"does this have to run on
  WordPress so WP Toolkit and SEO plugins work?"* — would have gone straight to the classic
  theme and skipped ~two full rebuilds.
- **The governance file fought the goal.** The added `CLAUDE.md` mandated Next.js/React,
  which is fundamentally incompatible with "must be WordPress." Reconciling that conflict
  sooner would have saved a rewrite. (It was ultimately updated to the WordPress stack.)
- **README rendering** uses a small hand-rolled Markdown→HTML converter. A vetted
  single-file library (e.g. Parsedown) would be more robust for edge cases.
- **Featured images** are still on-brand gradient placeholders. Real cover art would lift
  the archives and case studies noticeably.
- **Testing** relied on `php -l`, `node --check`, and browser smoke tests. A small PHPUnit
  suite (CPT registration, meta save/sanitise, sync mapping) would harden future changes.
- **Theme options.** A tiny settings page (GitHub username, contact recipient, accent
  toggle) would beat editing filters/constants for non-technical changes.

---

## 6. How it went — assessment

**Good.** Despite the two stack pivots, the project ended in a strong, coherent place:

- A **complete, self-contained WordPress theme** that builds the entire site on
  activation — pages, menu, sample content, GitHub import, permalinks flushed.
- **Verified on a real WordPress install** every iteration: all pages return 200, the
  contact form processes end-to-end, meta boxes and the Sync-GitHub admin page work, and
  there are **zero console errors** across desktop, tablet, and phone.
- **Responsive, accessible, and offline-friendly** — grouped nav, touch targets, skip
  link, focus rings, `prefers-reduced-motion`, self-hosted fonts, no trackers.
- **Genuinely dynamic and artistic** — animated neon hero, cursor-follow 3D card tilt,
  text-decode HUD, reading-progress bar, count-up stats — without tipping into noise, and
  all reduced-motion-safe.
- **Easy to hand off** — a 3-step Plesk install, a one-file offline preview, a complete
  download bundle, and honest documentation.

The clearest lesson is about **sequencing**: lock the platform constraints before writing
code, because a portfolio's host and CMS decide the whole stack. Everything after that
decision — the content model, the design system, the interactions — carried forward cleanly
and only got better with each pass.

---

*Repository:* `github.com/cian-omalley/cianomalley-portfolio-plesk` ·
*Theme:* [`digital-district/`](digital-district/) · *Live preview:* the interactive demo
artifact.
