# Demos

Standalone, self-contained demo pages (single `index.html` each, no dependencies).
Host each on its own `cianomalley.dev` subdomain — see `RUNBOOK.md` §9.

| Folder | Subdomain | What it is |
|---|---|---|
| `safepulse/` | `safepulse.cianomalley.dev` | Interactive concept: hold-to-SOS → routed to one team, transport-tier fallback, staff dashboard |
| `hermes/` | `hermes.cianomalley.dev` | Agent orchestration loop: tools · durable memory · task queue (click **Run**) |
| `captain-claw/` | `captain-claw.cianomalley.dev` | **Placeholder** playable mini-game — replace with the real Captain Claw build |

## Hosting (per demo)
1. Plesk → Websites & Domains → `cianomalley.dev` → **Add Subdomain** (e.g. `safepulse`).
2. Upload the folder's `index.html` into the subdomain's `httpdocs`.
3. Cloudflare → `cianomalley.dev` → add a CNAME (`safepulse` → `cianomalley.dev`); the
   `.dev` zone file lists these commented-out. Proxy **orange** after issuing SSL in Plesk.
4. Put the URL in the project's **Live URL** field in wp-admin → a "Visit live site" button appears.

> SafePulse & Hermes are grounded in the real projects. **Captain Claw is a stand-in** —
> tell Cian what it actually is and it'll be rebuilt for real.
