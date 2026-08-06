# Mailboxes & forwarding map

Self-hosted mail on the VPS (Plesk + Roundcube webmail). **4 real inboxes**;
every other address is a **forwarder** (no mailbox — it just redirects). This
keeps storage tiny and gives you clean role addresses.

## Real mailboxes (actual inboxes you log into)
| Inbox | Purpose |
|---|---|
| `cian@cianomalley.works` | You — personal / direct mail |
| `hello@cianomalley.works` | Public front door — people, inquiries, collaboration |
| `admin@cianomalley.works` | Operations & security — role accounts needing action |
| `website@cianomalley.works` | Automated & web-technical — notifications, reports, web roles |

## Forwarders (alias → inbox)

### → `cian@cianomalley.works`
- `dev@cianomalley.dev`

### → `hello@cianomalley.works`
- `contact@cianomalley.works`
- `info@cianomalley.works`
- `collab@cianomalley.works`
- `projects@cianomalley.works`
- `hello@cianomalley.dev`
- `projects@cianomalley.dev`

### → `admin@cianomalley.works`
- `postmaster@cianomalley.works`
- `abuse@cianomalley.works`
- `security@cianomalley.works`
- `hostmaster@cianomalley.works`
- `root@cianomalley.works`
- `alerts@cianomalley.works`
- `backups@cianomalley.works`
- `admin@cianomalley.dev`
- `postmaster@cianomalley.dev`
- `abuse@cianomalley.dev`
- `security@cianomalley.dev`
- `hostmaster@cianomalley.dev`
- `alerts@cianomalley.dev`

### → `website@cianomalley.works`
- `webmaster@cianomalley.works`
- `dmarc@cianomalley.works`
- `notifications@cianomalley.works`
- `webmaster@cianomalley.dev`
- `dmarc@cianomalley.dev`
- `deploy@cianomalley.dev`
- `bugs@cianomalley.dev`

## Notes
- Both domains need **mail service enabled** in Plesk (the `.dev` forwarders
  receive mail before redirecting), so this reverses the earlier "`.dev` mail
  disabled" note.
- DMARC aggregate reports go to `dmarc@` on each domain → `website@`.
- `postmaster@` / `abuse@` are required by RFC 2142; Plesk may pre-create them —
  the setup script updates them to forward if they already exist.
- Suggested **contact-form recipient** for the site: `contact@cianomalley.works`
  (→ `hello@`). Change with the `dd_contact_recipient` filter if you prefer.
- `plesk-setup.sh` creates all of the above (mailboxes + forwarders) in one run.
