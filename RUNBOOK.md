# Deployment runbook — Cian O'Malley portfolio

The complete, ordered guide to taking this from "VPS with Plesk" to "live site +
mail + demos." It says exactly what to do, what's already done, and what's left.

- **Server:** IONOS VPS `cian-prod-web-01`, Debian 13.6 + Plesk Obsidian 18.0.80, AMD EPYC 8-core, 480 GB NVMe
- **IPv4:** `212.227.29.230`  ·  **IPv6:** `2a02:2479:15:2300::1` (host in the `/80`)
- **Domains:** `cianomalley.works` (portfolio) · `cianomalley.dev` (demos)
- **Mail:** self-hosted (Plesk + Roundcube), 4 inboxes + forwarders

---

## Status at a glance

| Done ✅ | Still to do ⏳ |
|---|---|
| IONOS firewall `fw-cian-prod-web-01` (ports 22/80/443/8443/25/587/465/993) | Reverse DNS (PTR) for both IPs |
| VPS + Plesk installed (Debian 13.6, Obsidian 18.0.80) | Nameservers → Cloudflare (at Name.com) |
| IPs assigned & active | Run `plesk-setup.sh` (mail, forwarders, DKIM) |
| Theme built + all deploy files prepared | Import DNS zones into Cloudflare |
| Mail scheme designed (4 inboxes + forwarders) | Issue Let's Encrypt certs |
| Demo pages built (SafePulse, Hermes, Captain Claw) | Set Cloudflare proxy + Full (strict) |
| | Install WordPress + activate theme |
| | Publish content (drafts) |
| | Host the 3 demos on `.dev` subdomains |

---

## Before you begin (have ready)
- **SSH access:** an app like **Termius** (mobile) or Terminal/PowerShell → `ssh root@212.227.29.230`.
- **4 mailbox passwords** — you'll paste these into `plesk-setup.sh`.
- **Your current public IP** (google "what is my IP") — to lock down SSH/Plesk later.
- Set up an **SSH key** and disable password login early if you can (see §9).

---

## 1. IONOS — reverse DNS (PTR)  ⏳
IONOS Cloud Panel → **Network → Public IP** → select `212.227.29.230` → set
**Reverse DNS** to `server.cianomalley.works`. Repeat for the IPv6.
This is required before outbound mail (port 25) works and before mail servers
trust you. (The firewall is already done.)

## 2. DNS foundation — Name.com → Cloudflare  ⏳
1. Sign in to **Cloudflare** → **Add a domain** → `cianomalley.works` → **Free** plan.
2. It shows **two nameservers** (e.g. `xxx.ns.cloudflare.com`). Copy them.
3. Repeat step 1–2 for `cianomalley.dev`.
4. At **Name.com** → My Domains → each domain → **Manage Nameservers**:
   - **Disable DNSSEC first** (if on), then replace the nameservers with Cloudflare's two.
5. Wait until Cloudflare shows each domain **Active** (minutes–hours; it emails you).
   Re-enable **DNSSEC in Cloudflare** only after it's active.

## 3. SSH in and run the setup script  ⏳
1. `ssh root@212.227.29.230`
2. Open `plesk-setup.sh`, set the **4 mailbox passwords** (`PW_CIAN`, `PW_HELLO`,
   `PW_ADMIN`, `PW_WEBSITE`). Confirm `IPV6` with `ip -6 addr show` (usually `…::1`).
3. Upload it (Termius/SFTP) or paste it into `nano plesk-setup.sh`, then:
   ```
   bash plesk-setup.sh
   ```
4. It: sets the hostname, creates both domains, makes the **4 inboxes**, creates all
   **forwarders** (see `mail-map.md`), enables **DKIM**, and **prints two DKIM
   records** — copy both. Any step that can't run prints a `!!` line with the UI path.

> If a `!!` line appears (Plesk CLI flags vary by build), tell me the exact line and
> I'll give you the precise Obsidian 18.0.80 command or UI click.

## 4. Cloudflare — import DNS + add DKIM  ⏳
For **each** domain: Cloudflare → the domain → **DNS → Records → Import** → upload the
matching `.zone` file. Then:
- Add the **DKIM TXT** record for that domain (name `default._domainkey`) using the value
  the script printed in §3.
- **Proxy status:** set `@` and `www` to **DNS only (grey)** for now; keep `server`,
  `mail`, `webmail` **grey always** (never proxy mail).

## 5. Plesk — issue Let's Encrypt  ⏳
DNS is resolving now. Run the two commands the script printed at the end (or use the UI:
each domain → **SSL/TLS Certificates → Install free Let's Encrypt**, include `www`,
`mail`, `webmail`). Then enable **Redirect HTTP→HTTPS**, **Secure mail**, **Secure
webmail**. Also issue a cert for **`server.cianomalley.works`** (Tools & Settings →
SSL/TLS) and assign it to **Plesk** + **Mail server** — then use
`https://server.cianomalley.works:8443` instead of the IP.

## 6. Cloudflare — turn on the proxy + strict SSL  ⏳
- Switch `@` and `www` (both domains) to **Proxied (orange)**.
- **SSL/TLS → Overview → Full (strict)**. ⚠️ Never "Flexible" with Plesk (redirect loop).
- **SSL/TLS → Edge Certificates:** Always Use HTTPS, Automatic HTTPS Rewrites, TLS 1.3.
- In Plesk, disable the local DNS zone for each domain (Cloudflare is authoritative).

## 7. WordPress + theme  ⏳
1. Plesk → **WordPress Toolkit → Install** on `cianomalley.works` (path blank),
   **PHP 8.3 FPM**, a unique admin username, a generated password, your external email.
2. Set site **Title** `Cian O'Malley — Developer, creator and technical project builder`,
   tagline `Developer` (Settings → General).
3. **PHP Settings:** memory_limit 256M · upload_max_filesize 64M · post_max_size 64M ·
   max_execution_time 120 · opcache On.
4. **Appearance → Themes → Add New → Upload Theme** → `digital-district.zip` → Install → Activate.
5. **Settings → Permalinks → Post name → Save.**
6. **Projects → Sync GitHub** (add `define('CIAN_GITHUB_TOKEN','…')` to `wp-config.php` first if you want private repos).

## 8. Publish content  ⏳
Everything seeds as **drafts**. In wp-admin, open **Projects / Client Work / Guides /
Reviews / Posts**, review each, and **Publish** the ones you want live. Clone the
`TEMPLATE —` drafts to add new items. Full guide: `AUTHORING.md`.

## 9. The demos on `.dev`  ⏳
Three standalone demo pages are in **`demos/`**: `safepulse/`, `hermes/`, `captain-claw/`.
Host each on its own subdomain:
1. Plesk → **Websites & Domains → `cianomalley.dev` → Add Subdomain** → e.g. `safepulse`.
2. Upload that demo's `index.html` (+ any assets) into the subdomain's `httpdocs`
   (File Manager or SFTP).
3. Cloudflare → `cianomalley.dev` zone → add a **CNAME** `safepulse` → `cianomalley.dev`
   (the zone file already lists these commented-out; uncomment them). Proxy **orange**
   after the subdomain has an SSL cert (Plesk → the subdomain → Let's Encrypt).
4. Repeat for `hermes` and `captain-claw`.

Result: `safepulse.cianomalley.dev`, `hermes.cianomalley.dev`, `captain-claw.cianomalley.dev`.
Link them from each project's **Live URL** field in wp-admin so the "Visit live site"
button appears.

## 10. Harden (after everything works)  ⏳
- SSH: add a key, disable password login (`PasswordAuthentication no`), keep Fail2Ban on.
- IONOS firewall: restrict **22** and **8443** to your public IP (only after you've
  confirmed it — don't lock yourself out).
- Plesk: enable **2FA** for the admin.
- Backups: set a Plesk scheduled backup (DB + files); optionally an off-site copy.
  Remember GitHub backs up the theme source, **not** the DB, uploads, or mail.

---

## Email — how it works once live
- **Webmail:** `https://webmail.cianomalley.works` (Roundcube) — log in with a mailbox
  address + its password.
- **The 4 inboxes** are `cian@`, `hello@`, `admin@`, `website@` (`.works`). Everything
  else forwards to one of them by function — see `mail-map.md`.
- **Deliverability checklist:** PTR set (§1) · SPF, DKIM, DMARC records present (§4) ·
  start DMARC at `p=none`, then tighten to `quarantine` then `reject` once mail passes.
- Test with [mail-tester.com](https://www.mail-tester.com): send it a mail from
  `cian@cianomalley.works` and aim for 10/10.

---

## Troubleshooting
| Symptom | Fix |
|---|---|
| Site redirect loop | Cloudflare SSL is "Flexible" — set **Full (strict)**. |
| Let's Encrypt fails | The record must be **grey (DNS only)** and resolving; wait for DNS, retry. |
| Mail won't send | PTR not set (§1), or port 25 blocked upstream — check IONOS. |
| Mail lands in spam | Check SPF/DKIM/DMARC align; run mail-tester. |
| `/projects/` 404s | Re-save **Settings → Permalinks**. |
| A `plesk bin` step failed (`!!`) | Do it in the UI (path shown), or send me the line. |
| Fonts/theme look plain | Ensure the full `digital-district.zip` uploaded (fonts are inside it). |

## What's still to do (short list)
1. PTR (both IPs) in IONOS.
2. Nameservers → Cloudflare at Name.com.
3. Run `plesk-setup.sh` (set passwords first).
4. Import DNS zones + DKIM into Cloudflare.
5. Issue Let's Encrypt, then proxy + Full (strict).
6. Install WordPress + activate theme + permalinks + Sync GitHub.
7. Publish the drafts you want.
8. Host the 3 demos on `.dev` subdomains.
9. Harden (SSH key, restrict ports, 2FA, backups).

Send me a screenshot at any step and I'll walk you through that exact screen.
