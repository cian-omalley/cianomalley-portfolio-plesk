# Deploying to Plesk

This site builds to plain static files plus one optional PHP script. There is **no
Node runtime requirement on the server** and **no builder** — Plesk just serves the
files. Two ways to do it.

---

## Option A — Build locally, upload `dist/` (simplest, recommended)

1. **Build** on your machine:
   ```bash
   npm install
   npm run build
   ```
   This produces `dist/` containing `index.html`, the per-page folders, hashed CSS/JS
   and fonts under `_astro/`, `favicon.svg`, `robots.txt`, `sitemap.xml`, and
   `contact.php`.

2. **Upload** the **contents of `dist/`** (not the folder itself) into the domain's
   document root in Plesk:
   - Plesk → **Websites & Domains** → your domain → **File Manager**
   - Open `httpdocs/`, remove the default placeholder files, and upload everything from
     `dist/`. (Zip `dist/`, upload the zip, then "Extract Files" is fastest.)

3. **Set the contact address** — edit `httpdocs/contact.php` and change:
   ```php
   $TO = getenv('CIAN_CONTACT_TO') ?: 'you@example.com'; // <-- your address
   ```
   Or, instead of editing the file, set an env var in Plesk → **PHP Settings** /
   Additional configuration directives:
   ```
   env[CIAN_CONTACT_TO] = you@yourdomain.tld
   ```

4. **Mail** — make sure the domain's **Mail Service** is enabled (Plesk → Mail) so PHP
   `mail()` delivers. If you use an external mailbox, configure Plesk's outgoing mail /
   SMTP accordingly.

5. **HTTPS** — enable the free **Let's Encrypt** certificate (Plesk → SSL/TLS
   Certificates) and turn on "Redirect from HTTP to HTTPS".

Done. Because Astro emits clean directory URLs (`/projects/`), Plesk's Nginx/Apache
serves each folder's `index.html` automatically — no rewrite rules needed.

---

## Option B — Build on the server with Node (Plesk "Node.js" toolkit)

Only needed if you want to `git pull` and build on the box.

1. Plesk → **Websites & Domains** → **Node.js** → enable it for the domain.
2. Upload/clone the repo into the domain (outside `httpdocs`, e.g. the app root).
3. Set **Application Startup File** is not required — this is a static build. Instead use
   the **Run Script** panel:
   - `npm install`
   - `npm run build`
4. Point the domain's **Document Root** at `.../dist` (Plesk → Hosting Settings →
   Document root), or copy `dist/*` into `httpdocs`.
5. Set `CIAN_CONTACT_TO` under **Node.js → Environment variables** or in PHP settings as
   in Option A.

> Static output means you do **not** need to keep a Node process running. The Node
> toolkit here is only a build convenience.

---

## Updating the site later

- Change content in `src/data/site.js` (or any page in `src/pages/`).
- `npm run build`.
- Re-upload `dist/` (Option A) or re-run the build script (Option B).

## Custom domain / both domains

The original project uses `cianomalley.works` (primary) and `cianomalley.dev`
(showcase). Set `site:` in `astro.config.mjs` to whichever domain this deployment
serves so canonical URLs, Open Graph, and the sitemap are correct, then rebuild.

## Troubleshooting

| Symptom | Fix |
|---|---|
| Contact form shows "relay is offline" | Domain mail service disabled, or `mail()` blocked — enable Plesk Mail or wire SMTP. |
| Fonts look like system defaults | The `_astro/` folder wasn't uploaded — upload the **whole** `dist/`. |
| 3D hero not showing | Expected on very old browsers / no WebGL — the CSS fallback is intentional. |
| `/projects` 404s | Document root is wrong, or directory index disabled — point it at the folder containing `index.html`. |
