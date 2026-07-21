# Deploying to Plesk

The site is a **Next.js static export**: `pnpm build` writes plain HTML/CSS/JS to
`out/`, plus the one PHP script. There is **no Node runtime requirement on the
server** — Plesk just serves the files.

---

## Option A — Build locally, upload `out/` (simplest, recommended)

1. **Build** on your machine:

   ```bash
   pnpm install
   pnpm build
   ```

   This produces `out/` containing `index.html`, `404.html`, the hashed assets
   under `_next/`, self-hosted fonts, `favicon.svg`, `robots.txt`, `sitemap.xml`,
   and `contact.php`.

2. **Upload** the **contents of `out/`** (not the folder itself) into the domain's
   document root in Plesk:
   - Plesk → **Websites & Domains** → your domain → **File Manager**
   - Open `httpdocs/`, remove the default placeholder files, and upload everything
     from `out/`. (Zip `out/`, upload the zip, then "Extract Files" is fastest.)

3. **Set the contact address** — edit `httpdocs/contact.php`:

   ```php
   $TO = getenv('CIAN_CONTACT_TO') ?: 'you@example.com'; // <-- your address
   ```

   Or set it without editing the file, in Plesk → **PHP Settings** / Additional
   directives:

   ```
   env[CIAN_CONTACT_TO] = you@yourdomain.tld
   ```

4. **Mail** — enable the domain's **Mail Service** (Plesk → Mail) so PHP `mail()`
   delivers, or configure outgoing SMTP.

5. **HTTPS** — enable the free **Let's Encrypt** certificate (Plesk → SSL/TLS
   Certificates) and turn on "Redirect from HTTP to HTTPS".

Because the export uses `trailingSlash: true`, every route is a folder with its
own `index.html`, so Plesk's Nginx/Apache serves it with no rewrite rules.

---

## Option B — Build on the server with Node (Plesk "Node.js" toolkit)

Only needed if you want to `git pull` and build on the box.

1. Plesk → **Websites & Domains** → **Node.js** → enable it for the domain.
2. Upload/clone the repo into the domain (outside `httpdocs`).
3. In the Node.js panel, install deps and build:
   - `npm install -g pnpm` (or use `corepack enable`)
   - `pnpm install`
   - `pnpm build`
4. Point the domain's **Document Root** at `.../out`, or copy `out/*` into
   `httpdocs`.
5. Set `CIAN_CONTACT_TO` under PHP settings as in Option A.

> The output is static, so you do **not** keep a Node process running — the Node
> toolkit is only a build convenience.

---

## Updating the site later

- Change content in `src/data/site.ts` (or any component in `src/components/`).
- `pnpm build`.
- Re-upload `out/` (Option A) or re-run the build (Option B).

## Custom domain

Set `metadataBase` / `site` URLs by editing `identity.domains.primary` in
`src/data/site.ts` (used for canonical URLs, Open Graph, and JSON-LD), then
rebuild.

## Troubleshooting

| Symptom                               | Fix                                                                                                                |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Contact form shows "relay is offline" | Domain mail service disabled or `mail()` blocked — enable Plesk Mail or wire SMTP.                                 |
| Fonts look like system defaults       | The `_next/` folder wasn't uploaded — upload the **whole** `out/`.                                                 |
| 3D hero not showing                   | Expected with no WebGL — the CSS grid backdrop is the fallback.                                                    |
| Assets 404 under a subpath            | The export assumes the site root. Serve it at the domain root, or set `basePath` in `next.config.mjs` and rebuild. |
