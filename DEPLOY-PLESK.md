# Deploying on Plesk

This is a WordPress theme, so you run it on a WordPress site managed by Plesk's **WP
Toolkit**. No build step, no Node runtime.

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
- **imports every repository** from your GitHub account into **Projects**.

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

## Re-syncing GitHub projects

wp-admin → **Projects → Sync GitHub** re-imports your repositories any time; it also refreshes
daily via cron. Titles and write-ups you edit by hand are preserved. To import from a
different account, add to a small mu-plugin or the theme:

```php
add_filter( 'dd_github_user', fn() => 'your-github-username' );
```

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
