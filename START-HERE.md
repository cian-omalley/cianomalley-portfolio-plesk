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
| **digital-district.zip** | The theme — upload to WordPress |
| **demos/** | Standalone demo pages for SafePulse, Hermes, Captain Claw |

## The order (one line each)
1. **IONOS** — set reverse DNS (PTR) for both IPs → `server.cianomalley.works`.
2. **Cloudflare + Name.com** — add domains to Cloudflare, point Name.com nameservers at it.
3. **SSH** — `bash plesk-setup.sh` (creates mail, prints DKIM records).
4. **Cloudflare** — import both zone files + the 2 DKIM records; proxy grey for now.
5. **Plesk** — issue Let's Encrypt (command printed by the script).
6. **Cloudflare** — flip `@`/`www` to orange, SSL → Full (strict).
7. **WordPress** — install, upload `digital-district.zip`, activate, permalinks, Sync GitHub.
8. **Content** — publish the drafts you want (see AUTHORING.md).
9. **Demos** — create the `.dev` subdomains and upload the `demos/` pages.

## The 2 things only you can do
- Set the **4 mailbox passwords** in `plesk-setup.sh`.
- Set **reverse DNS (PTR)** in IONOS for both IPs (mail won't send otherwise).
