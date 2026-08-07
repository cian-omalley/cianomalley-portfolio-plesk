# START HERE

Everything for deploying the Cian O'Malley portfolio on the IONOS + Plesk +
Cloudflare stack. Read **`RUNBOOK.md`** for the full step-by-step; this is the
30-second map.

## The files
| File | What it is |
|---|---|
| **RUNBOOK.md** | The detailed guide — every step, in order, with commands + what's still to do |
| **INFRA.md** | Reference: hostnames, firewall, DNS tables, SSL order |
| **mail-map.md** | Your 4 inboxes + every forwarder |
| **AUTHORING.md** | How to add projects / blogs / media after launch |
| **plesk-setup.sh** | Run on the VPS over SSH (mailboxes, forwarders, DKIM) |
| **cianomalley.works.zone** / **cianomalley.dev.zone** | Import into Cloudflare |
| **digital-district.zip** | The theme — upload to WordPress. Includes built-in Maintenance Mode (Settings → Maintenance Mode once installed) |
| **demos/** | Standalone demo pages for SafePulse, Hermes, Captain Claw |

## The order (one line each)
1. ✅ **IONOS firewall** — done.
2. ✅ **SSH + `plesk-setup.sh`** — done: hostname, both domains, 4 mailboxes, all 27
   forwarders, DKIM enabled on both domains (keys captured).
3. ⏳ **IONOS** — set reverse DNS (PTR) for both IPs → `server.cianomalley.works`.
4. ⏳ **Cloudflare + Name.com** — confirm domains are on Cloudflare and nameservers
   at Name.com point at it.
5. ⏳ **Cloudflare** — finish importing both zone files + add the 2 DKIM TXT
   records; proxy grey for now.
6. ⏳ **Plesk** — issue Let's Encrypt (command printed by the script).
7. ⏳ **Cloudflare** — flip `@`/`www` to orange, SSL → Full (strict).
8. ⏳ **WordPress** — install, upload `digital-district.zip`, activate, permalinks, Sync GitHub.
9. ⏳ **Content** — publish the drafts you want (see AUTHORING.md); optionally flip on
   **Settings → Maintenance Mode** while you work.
10. ⏳ **Demos** — create the `.dev` subdomains and upload the `demos/` pages.

## The 1 thing only you can still do
- Set **reverse DNS (PTR)** in IONOS for both IPs (mail won't send outbound otherwise).
