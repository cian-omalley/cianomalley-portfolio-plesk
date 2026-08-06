# Infrastructure & deployment reference

Consolidated, decoded notes for deploying this portfolio on the live server —
distilled from the IONOS + Plesk + Cloudflare planning docs. This is the single
source of truth for hostnames, firewall rules, and DNS records.

> ⚠️ **This repo is public.** Never commit the real **origin IPv4/IPv6** or any
> secret here — Cloudflare's proxy only hides your origin if the IP isn't
> published. Keep exact IPs in your IONOS panel / a private note. Placeholders
> `<IPv4>` / `<IPv6>` are used below; get the real values from
> **IONOS → Network → Public IP**.

---

## At a glance
| | |
|---|---|
| **Provider** | IONOS Cloud (VPS), server `cian-prod-web-01`, AMD EPYC-Milan 8 cores |
| **OS / panel** | **Debian 13.6** + **Plesk Obsidian 18.0.80** (already installed — do **not** reinstall) |
| **Disk** | 480 GB NVMe (no Block Storage needed) |
| **IPs** | IPv4 + IPv6 `…/80` assigned & active — **reverse DNS (PTR) not yet set** (needed for mail) |
| **Mail** | self-hosted (Plesk + Roundcube). 4 inboxes + forwarders — see [`deploy/mail-map.md`](deploy/mail-map.md). Both domains mail-enabled. |
| **Registrar** | Name.com |
| **Authoritative DNS** | Cloudflare (Free plan) |
| **Primary domain** | `cianomalley.works` (portfolio) |
| **Demo domain** | `cianomalley.dev` (project demos as subdomains) |
| **Servers** | one VPS (no load balancer, no private network) |

## Hostnames & naming scheme
| Purpose | Value |
|---|---|
| IONOS server display name | `cian-prod-web-01` |
| Linux / Plesk hostname (FQDN) | `server.cianomalley.works` |
| Main website | `cianomalley.works` |
| Main website alias | `www.cianomalley.works` (alias, **not** a separate domain) |
| Mail hostname | `mail.cianomalley.works` |
| Webmail hostname | `webmail.cianomalley.works` |
| Demo index | `cianomalley.dev` |
| Individual demos | `<project>.cianomalley.dev` |
| Firewall policy name | `fw-cian-prod-web-01` |

The Plesk hostname must be the FQDN `server.cianomalley.works`, not an internal
label. Reverse DNS (PTR) for **both** IPv4 and IPv6 must be set to
`server.cianomalley.works` (forward-confirmed reverse DNS) — required before
outbound SMTP (port 25) is unblocked.

---

## IONOS firewall (external) — inbound rules
> ✅ **Confirmed done** — policy `fw-cian-prod-web-01` is Active with exactly these
> Allow rules (22, 80, 443, 8443, 25, 587, 465, 993). 8447 is optional (only for
> the Plesk installer; open it temporarily if component installs fail).

**Open:**
| Port | Proto | Source | Purpose |
|---|---|---|---|
| 22 | TCP | your IP (after setup) | SSH / SFTP |
| 80 | TCP | all | HTTP → HTTPS redirect + cert validation |
| 443 | TCP | all | Websites, demos, webmail |
| 8443 | TCP | your IP (after setup) | Plesk panel |
| 8447 | TCP | all | Plesk installer / updates |
| 25 | TCP | all | Inbound SMTP (needs PTR + SPF first) |
| 587 | TCP | all | SMTP submission (authenticated outgoing) |
| 465 | TCP | all | SMTPS (implicit TLS submission) |
| 993 | TCP | all | IMAPS (secure mail access) |

**Close / leave closed:**
| Port | Why |
|---|---|
| 110 | plaintext POP3 |
| 143 | IMAP STARTTLS (use 993) |
| 995 | POP3S (unless you use POP3) |
| 8880 | insecure Plesk panel (use 8443) |
| 21 | FTP (use SFTP over 22) |
| 3306 / 5432 | databases must not be public |
| 53 (TCP/UDP) | DNS — Cloudflare is authoritative |

> During setup you may temporarily open 22 and 8443 to all; **verify your current
> public IP first**, then restrict, or you can lock yourself out. Enable
> **Fail2Ban** in Plesk regardless. Don't manage rules with both `firewalld` and
> the Plesk Firewall extension at once.

---

## Cloudflare DNS — `cianomalley.works`
Create these (add AAAA only after IPv6 is verified on the server). Keep
`server`, `mail`, `webmail` **DNS-only (grey)** — never proxy mail.

| Type | Name | Content | Proxy |
|---|---|---|---|
| A | `@` | `<IPv4>` | DNS only *(→ Proxied after SSL)* |
| AAAA | `@` | `<IPv6>` | DNS only *(→ Proxied after SSL)* |
| CNAME | `www` | `cianomalley.works` | DNS only *(→ Proxied after SSL)* |
| A | `server` | `<IPv4>` | 🔘 DNS only (always) |
| AAAA | `server` | `<IPv6>` | 🔘 DNS only (always) |
| CNAME | `mail` | `server.cianomalley.works` | 🔘 DNS only |
| CNAME | `webmail` | `server.cianomalley.works` | 🔘 DNS only |

**Mail records** (always DNS-only):
| Type | Name | Content |
|---|---|---|
| MX | `@` | `server.cianomalley.works` (priority **10**) |
| TXT (SPF) | `@` | `v=spf1 a:server.cianomalley.works mx ip4:<IPv4> ip6:<IPv6> ~all` |
| TXT (DKIM) | `default._domainkey` | **copy from Plesk** — Mail Settings → *How to configure external DNS*. Do **not** invent it. |
| TXT (DMARC) | `_dmarc` | `v=DMARC1; p=none; rua=mailto:dmarc@cianomalley.works; adkim=s; aspf=s; pct=100` |

- If IPv6 mail isn't active yet, drop the `ip6:` part of SPF: `v=spf1 a:server.cianomalley.works mx ip4:<IPv4> ~all`.
- DMARC policy: start `p=none` → `p=quarantine` → eventually `p=reject`, once SPF/DKIM/DMARC pass consistently.
- If Plesk also lists `autoconfig` / `autodiscover` / SRV records, copy them to Cloudflare exactly as shown.

## Cloudflare DNS — `cianomalley.dev`
| Type | Name | Content | Proxy |
|---|---|---|---|
| A | `@` | `<IPv4>` | DNS only *(→ Proxied after SSL)* |
| AAAA | `@` | `<IPv6>` | DNS only *(→ Proxied after SSL)* |
| CNAME | `www` | `cianomalley.dev` | DNS only *(→ Proxied after SSL)* |

Per demo (create one each, no wildcard initially):
| Type | Name | Content | Proxy |
|---|---|---|---|
| CNAME | `portfolio-demo` | `cianomalley.dev` | Proxied (after SSL) |
| CNAME | `ai-os` | `cianomalley.dev` | Proxied (after SSL) |
| CNAME | `<project>` | `cianomalley.dev` | Proxied (after SSL) |

## Nameservers (Name.com → Cloudflare)
1. Add each domain to Cloudflare (Free plan) → copy the **two assigned nameservers**.
2. Name.com → My Domains → the domain → **Manage Nameservers** → remove existing, add Cloudflare's two, save.
3. **Disable DNSSEC** at Name.com before switching; re-enable Cloudflare DNSSEC only once the domain resolves through Cloudflare.
4. In Plesk, after Cloudflare is live: Websites & Domains → each domain → **DNS Settings → Disable** (Cloudflare is authoritative).

---

## SSL — order of operations (avoids redirect loops)
1. Keep the website records **grey (DNS only)** first: `cianomalley.works`, `www`, `mail`, `webmail`, and the `.dev` equivalents.
2. **Plesk → the domain → SSL/TLS Certificates → Install free Let's Encrypt**, including `cianomalley.works`, `www`, `mail`, `webmail`. Enable **Redirect HTTP→HTTPS**, **Secure mail**, **Secure webmail**.
3. Separately issue a Let's Encrypt cert for **`server.cianomalley.works`** (Tools & Settings → SSL/TLS Certificates) and assign it to **Plesk** + **Mail server**. Then use `https://server.cianomalley.works:8443` instead of the IP.
4. Issue a cert for `cianomalley.dev` + `www`, and one per demo subdomain as you add them. (Wildcard is possible but needs a manual `_acme-challenge` TXT in Cloudflare since DNS is external.)
5. Flip the website records to **Proxied (orange)**: `@`, `www` (keep `server`/`mail`/`webmail` grey).
6. **Cloudflare → SSL/TLS → Overview → `Full (strict)`.** ⚠️ Never "Flexible" with Plesk.
7. **Cloudflare → SSL/TLS → Edge Certificates:** Universal SSL, Always Use HTTPS, Automatic HTTPS Rewrites, TLS 1.3. Hold off on long **HSTS** until everything's been stable for days.

---

## Plesk setup checklist
- First login as `root` (IONOS initial password) → complete config, **change root password**, set admin password, external email for notifications, **enable 2FA**.
- **Tools & Settings → Server Settings** → hostname `server.cianomalley.works`.
- **Tools & Settings → IP Addresses** → confirm IPv4 + IPv6 appear (Reread IP if not; don't add AAAA in Cloudflare until Plesk/Ubuntu see the v6).
- **Add/Remove Components:** Nginx, Apache, MariaDB/MySQL, PHP 8.3, PHP-FPM, WordPress Toolkit, Git, Let's Encrypt, SSL It!, Mail server, Dovecot IMAP, **Roundcube webmail**, Fail2Ban. Apply all updates.
- **Add domains:** `cianomalley.works` (doc root `httpdocs`, mail **enabled**); `cianomalley.dev` (separate doc root, mail **disabled**). `www` = alias of `.works`; `.dev` is **not** an alias of `.works`.

## WordPress + theme deploy
- **WordPress Toolkit → Install** on `cianomalley.works` (path blank = domain root), **PHP 8.3 FPM**, unique admin username, generated password, external email.
  - Site title: `Cian O'Malley — Developer, creator and technical project builder`; tagline `Developer`; address `https://cianomalley.works`.
- **PHP Settings:** `memory_limit 256M`, `upload_max_filesize 64M`, `post_max_size 64M`, `max_execution_time 120`, `opcache.enable On`.
- **Theme:** the theme is the **`digital-district`** folder — upload **`digital-district.zip`** (built by `./package.sh`), *not* the whole repo and *not* the complete release bundle. Appearance → Themes → Add New → Upload → Install → Activate.
  - On activation it builds the pages/menu and seeds projects + `TEMPLATE —` starters **as drafts** (nothing auto-published — see `AUTHORING.md`).
- **Settings → Permalinks →** Post name → Save (enables `/projects/`, `/work/`, `/guides/`, `/reviews/`).
- **Projects → Sync GitHub** to import public repos (add `CIAN_GITHUB_TOKEN` to `wp-config.php` for private ones — see `DEPLOY-PLESK.md`).

---

## Secrets & safety (never commit / never paste in full)
- Root password, Plesk admin password, WordPress admin password, database passwords, DKIM private key, Cloudflare API token — keep these in a password manager, out of this repo and out of chat.
- The **only** credential appropriate to share for remote help is a **revocable WordPress Application Password** (Users → Profile), which you revoke afterward.

## Open items to resolve
- **IPv6 discrepancy:** one IONOS screenshot showed no IPv6, another showed an assigned address. Confirm in IONOS → Network → Public IP that the IPv4 *and* IPv6 belong to the **same active server** before creating any AAAA records. Enter the full host address (not the `/80` prefix) in Cloudflare.
