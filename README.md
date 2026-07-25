# Cian O'Malley — Portfolio (WordPress theme for Plesk)

**Digital District** — a dynamic, animated cyberpunk-brutalist portfolio, built as a
hand-coded **WordPress theme** with **no page builder**. It runs on WordPress so it works
with **Plesk's WP Toolkit** and any free WordPress **SEO plugin** (Yoast, The SEO
Framework, Rank Math), and it showcases **client work** and **personal projects** through
custom post types with individual case-study pages.

> This repository is a **separate fork**. The original WordPress build lives in
> `cian-omalley/cianomalley-portfolio` and is untouched. The theme lives in
> [`digital-district/`](digital-district/).

## Why WordPress (no builder)

- Manage everything in `wp-admin`; install SEO/caching/security extensions from your Plesk
  subscription; update and back up through **WP Toolkit**.
- No proprietary page builder — just PHP templates, CSS, and vanilla JS.

## What it does

- **Client Work** and **Projects** are separate custom post types, each with an archive and
  an individual **case-study page** (`single-*.php`). Titles are clickable and **light up +
  animate** on hover.
- **Guides**, **Reviews**, and the native **Blog** each have archive + single templates.
  Reviews support a star rating; guides support a read-time.
- **Every item shows a status** — Projects: In Progress / Planning / Complete / Prototype /
  Research / Live; Client Work: Live / In Progress / Complete / Prototype; Guides:
  Published / In Progress / Planned; Reviews: a verdict + rating.
- **All your GitHub repositories import automatically** as Projects (see below), with a
  status derived from each repo, and a one-click re-sync.
- **Fluid, dynamic animations** — a custom cursor (dot + trailing ring that reacts to
  interactive elements), scroll reveals, count-up stats, magnetic buttons, an animated neon
  skyline canvas hero, and an `Esc`-accessible overlay menu. All respect
  `prefers-reduced-motion` and disable on touch.
- **Accessible** — semantic landmarks, skip link, focus rings, keyboard-operable menu, and
  a full no-JS baseline.
- **SEO-ready** — `title-tag` support (so SEO plugins own titles/meta), clean semantic
  markup, feeds, and post thumbnails.
- **Fully private / offline** — the three fonts (Space Grotesk, Inter, JetBrains Mono) are
  self-hosted in `assets/fonts/`. No Google Fonts, no CDNs, no third-party requests, no
  trackers.

## GitHub project sync

On activation the theme imports every public repository from your account
(`cian-omalley`, filterable via the `dd_github_user` filter) into **Projects**, mapping:

| Repo signal | Becomes |
| --- | --- |
| name → title, description → excerpt | Project title & summary |
| `html_url` / `homepage` | Repo & live links |
| `language` + topics | Technologies (`tech` taxonomy) |
| archived / recent push / empty | Status (Complete / In Progress / Planning) |

Re-sync any time from **wp-admin → Projects → Sync GitHub**; it also refreshes daily via
cron and **preserves** any titles/write-ups you edit by hand.

## Install

1. **Get the zip:** run `./package.sh` (produces `digital-district.zip`), or download it.
2. **Upload:** wp-admin → Appearance → Themes → Add New → Upload Theme → choose the zip →
   **Activate** (or install with WP Toolkit / drop the `digital-district` folder into
   `wp-content/themes/`).
3. On activation the theme creates the Home/About/Contact/Blog pages, a primary menu (Home,
   Work, Projects, Guides, Reviews, Blog, About, Contact), seeds honest guide/review topics,
   and imports your GitHub repos.
4. Set **Settings → Reading → Permalinks** to "Post name" (visit Settings → Permalinks and
   Save once) so the custom post type URLs resolve.

## Deploy on Plesk

See **[DEPLOY-PLESK.md](DEPLOY-PLESK.md)** for the full walkthrough (WP Toolkit install,
mail for the contact form, SEO plugin, HTTPS).

## Develop

```bash
# lint every PHP file
find digital-district -name '*.php' -print0 | xargs -0 -n1 php -l
# check the JS
node --check digital-district/assets/js/main.js
```

Content model lives in `inc/` (post types, meta boxes, GitHub sync, setup); design tokens in
`assets/css/tokens.css`; interactions in `assets/js/main.js`.

## License

[MIT](LICENSE).
