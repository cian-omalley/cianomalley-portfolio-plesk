# Deploy helpers

Copy-paste artifacts for standing up the site on the IONOS + Plesk + Cloudflare
stack. Full context is in [`../INFRA.md`](../INFRA.md).

| File | What it is | How to use |
| --- | --- | --- |
| `cianomalley.works.zone` | Cloudflare DNS zone (web + mail) | Cloudflare > cianomalley.works > DNS > Records > **Import** |
| `cianomalley.dev.zone` | Cloudflare DNS zone (demos) | Cloudflare > cianomalley.dev > DNS > Records > **Import** |
| `plesk-setup.sh` | Plesk/email setup helper | Run as root over SSH: `bash plesk-setup.sh` |

## ⚠ Placeholder vs. real values
The files **in this repo are placeholders** (`<IPv4>`, `<IPv6>`, `<STRONG-PASSWORD>`) —
this repo is **public**, so the real origin IP and any password must never be
committed (that's what keeps Cloudflare's proxy able to hide your origin).

Your **real, filled-in copies live in `/local/`**, which is **gitignored** and
never pushed. Edit those, use them, and keep them off GitHub.

## Order
1. Run `plesk-setup.sh` on the VPS → it enables DKIM and **prints the DKIM TXT record**.
2. Put your IPs (and the DKIM record) into the zone files → **import** them into Cloudflare.
3. Set proxy status (grey first), issue Let's Encrypt in Plesk, then flip to Proxied +
   Cloudflare SSL **Full (strict)** — see `INFRA.md` §SSL.
