# Deploying on Plesk (VPS or shared)

This is a WordPress theme, so you run it on a WordPress site managed by Plesk's **WP
Toolkit**. No build step, no Node runtime. It runs the same on a shared Plesk plan or a
full **Plesk VPS** — the VPS notes below (§7) only matter if you own the whole server.

**Requirements:** PHP **8.1+**, WordPress **6.5+**, MySQL/MariaDB. Nothing else — no Node,
no Composer, no external services. All fonts and assets are self-hosted (no CDN calls).

## ⚡ Quick install (3 steps)

1. **Get the zip.** Download `digital-district.zip` (or build it: run `./package.sh`).
2. **Install WordPress** on your domain in Plesk → **WP Toolkit → Install** (skip if you
   already have one). Set the site **Title** and **Tagline** under Settings → General —
   the theme uses them in the hero and footer.
3. **Upload & activate:** wp-admin → **Appearance → Themes → Add New → Upload Theme** →
   choose `digital-district.zip` → **Install** → **Activate**.

That's it. On activation the theme **builds the whole site for you** — pages (Home, About,
Contact, Blog), the nav menu, sample content, and it imports your GitHub repositories into
Projects. Permalinks are flushed automatically, so `/work/`, `/projects/`, `/guides/`, and
`/reviews/` work immediately. Then just enable HTTPS and mail (below).

---

## 1. Get WordPress running

- Plesk → **WordPress** (WP Toolkit) → **Install** on your domain (e.g. `cianomalley.works`),
  or attach an existing install.
- Set **Settings → General** site title/tagline (the theme uses these in the hero and
  footer).

## 2. Install the theme

**Option A — upload the zip (simplest):**

1. From this repo: `cd digital-district && zip -r ../digital-district.zip .`
2. wp-admin → **Appearance → Themes → Add New → Upload Theme** → choose `digital-district.zip`
   → **Install** → **Activate**.

**Option B — WP Toolkit / file manager:**

- Copy the `digital-district/` folder into `wp-content/themes/` (via WP Toolkit's file
  manager, SFTP, or Git), then activate it under Appearance → Themes.

On activation the theme:

- creates the **Home, About, Contact, Blog** pages and sets a static front page,
- builds the **primary menu** (Home · Work · Projects · Guides · Reviews · Blog · About ·
  Contact),
- seeds honest **guide** and **review** topics,
- seeds a **curated set of detailed project write-ups** into **Projects** (long-form
  case studies with architecture and breakdowns), and refreshes public repos from GitHub
  on top of them.

## 3. Fix permalinks (one time)

Visit **Settings → Permalinks**, choose **Post name**, and **Save**. This flushes rewrite
rules so `/work/`, `/projects/`, `/guides/`, `/reviews/`, and the case-study pages resolve.

## 4. Contact form email

The contact form sends with `wp_mail()`. In Plesk → **Mail**, enable the domain's mail
service (or configure outgoing SMTP) so messages deliver. Recipient defaults to the site
admin email; change it with the `dd_contact_recipient` filter if needed.

## 5. SEO plugin (from your Plesk subscription)

Install a free SEO plugin — **The SEO Framework**, **Yoast SEO**, or **Rank Math**. The theme
declares `title-tag` support and never hard-codes titles/meta, so the plugin fully controls
titles, descriptions, Open Graph, and sitemaps. The custom post types (`project`,
`client_work`, `guide`, `review`) are public and indexable, so they appear in the plugin's
sitemap automatically.

## 6. HTTPS & performance

- Plesk → **SSL/TLS Certificates** → issue a free **Let's Encrypt** cert and force HTTPS.
- Optional: a caching plugin from your subscription (e.g. a free page-cache) — the theme is
  static-friendly and cache-safe.

## 7. Running on a Plesk VPS (server owners)

Everything above is enough on shared hosting. On a VPS you control the whole stack, so a
few extra settings make it fast and tidy. None are required.

- **PHP version & limits.** Plesk → **PHP Settings** for the domain: select **PHP 8.1–8.3
  (FPM)**. Sensible values: `memory_limit 256M`, `upload_max_filesize 64M`,
  `post_max_size 64M`, `max_execution_time 120`, `opcache.enable On`. OPcache alone is a
  large, free speed win for WordPress.
- **Database.** Plesk installs **MariaDB/MySQL** for you; WP Toolkit wires it up. No manual
  config needed. Take a Plesk scheduled backup of the domain + database.
- **Object cache (optional).** If you enable **Redis** in Plesk, drop in a Redis object-cache
  plugin from your subscription — the theme is cache-safe and benefits, but works fine
  without it.
- **Web server.** Plesk's **nginx + Apache** default is fine as-is. If you run
  **nginx-only**, enable Plesk's static-file caching for the domain; the theme serves its CSS,
  JS and woff2 fonts as ordinary static files.
- **Cloudflare (optional).** Point the domain through Cloudflare for TLS, caching and — if
  you self-host behind a home connection first — a **Tunnel** so the origin IP is never
  exposed. The theme makes no external calls, so a strict Cloudflare cache/CSP won't break it.
- **Cron.** WordPress's pseudo-cron drives the daily GitHub re-sync. For a busy VPS, disable
  it (`define( 'DISABLE_WP_CRON', true );`) and add a real cron in Plesk → **Scheduled Tasks**
  hitting `wp-cron.php` every 15 minutes.

## Re-syncing GitHub projects

wp-admin → **Projects → Sync GitHub** re-imports your repositories any time; it also refreshes
daily via cron. Titles and write-ups you edit by hand are preserved. To import from a
different account, add to a small mu-plugin or the theme:

```php
add_filter( 'dd_github_user', fn() => 'your-github-username' );
```

### Importing private repositories

By default the sync only sees **public** repositories. To include **private** ones, add a
GitHub personal-access token to `wp-config.php` (never the database or a template):

```php
// wp-config.php — above the "That's all, stop editing" line.
define( 'CIAN_GITHUB_TOKEN', 'github_pat_...' );
```

Use a **fine-grained** token scoped to your account with read-only **Contents** and
**Metadata** permissions — nothing more. With it set, the sync switches to the authenticated
API and imports private repos too. Private projects are flagged and shown with a **"Private
repository"** note instead of a dead public GitHub link, so visitors never hit a 404. Revoke
or rotate the token any time in GitHub → Settings → Developer settings; the sync silently
falls back to public-only if it's removed.

> The theme also ships a **curated set of detailed project write-ups** (seeded on
> activation), so even before the first sync — or with the server offline — every project
> page is fully fleshed out. The sync refreshes public repos on top of that and leaves your
> hand-written bodies intact.

## Adding client work

wp-admin → **Client Work → Add New**: title, featured image, the write-up in the editor, an
excerpt for the card, and the **Details** box (client, status, services, year, live URL).
It appears on the homepage and the `/work/` archive with its own case-study page.

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| `/work/` or project pages 404 | Re-save **Settings → Permalinks** (Post name). |
| Contact form doesn't send | Enable the domain mail service in Plesk, or configure SMTP. |
| GitHub sync failed notice | The server must reach `api.github.com`; click **Sync GitHub** again. |
| Fonts look plain | Ensure `assets/fonts/` uploaded with the theme — fonts are self-hosted, no external calls. |
| Custom cursor not shown | Expected on touch devices / reduced-motion — the native cursor is used. |
