#!/usr/bin/env bash
# =============================================================================
# Plesk setup helper — Cian O'Malley portfolio   (PLACEHOLDER copy for the repo)
# -----------------------------------------------------------------------------
# Target: Debian 13 + Plesk Obsidian 18.0.x. Run as ROOT over SSH:  bash plesk-setup.sh
# Safe to re-run: every step checks first and never deletes anything.
# If a step prints "!!", finish that one in the Plesk UI (path shown).
#
# Fill these in (your LOCAL copy has real values; this repo copy uses placeholders):
# -----------------------------------------------------------------------------
DOMAIN_MAIN="cianomalley.works"
DOMAIN_DEV="cianomalley.dev"
HOSTNAME_FQDN="server.cianomalley.works"
IPV4="<IPv4>"                 # IONOS > Network > Public IP
IPV6="<IPv6>"                 # full host address (not the /80 prefix); "" if none
SERVICE_PLAN="Unlimited"
ADMIN_EMAIL="you@example.com"  # external address for Let's Encrypt / notices

# The 4 real inbox passwords — set strong values (your LOCAL copy only):
PW_CIAN="<PW-CIAN>"
PW_HELLO="<PW-HELLO>"
PW_ADMIN="<PW-ADMIN>"
PW_WEBSITE="<PW-WEBSITE>"
# -----------------------------------------------------------------------------

PLESK="plesk bin"
say(){ printf '\n\033[1;36m== %s\033[0m\n' "$*"; }

mkbox(){ # addr pass
  if $PLESK mail --info "$1" >/dev/null 2>&1; then echo "  exists: $1"; else
    $PLESK mail --create "$1" -mailbox true -passwd "$2" && echo "  mailbox: $1" \
      || echo "  !! create mailbox $1 in UI: Mail > Create Email Address"
  fi
}
mkfwd(){ # addr target
  if $PLESK mail --info "$1" >/dev/null 2>&1; then
    $PLESK mail --update "$1" -mailbox false -forwarding true -forwarding-addresses "set:$2" >/dev/null 2>&1 \
      && echo "  fwd (upd): $1 -> $2" || echo "  !! set forward $1 -> $2 in UI"
  else
    $PLESK mail --create "$1" -mailbox false -forwarding true -forwarding-addresses "set:$2" \
      && echo "  fwd: $1 -> $2" || echo "  !! create forward $1 -> $2 in UI"
  fi
}

# 1) Hostname -----------------------------------------------------------------
say "Set hostname -> $HOSTNAME_FQDN"
hostnamectl set-hostname "$HOSTNAME_FQDN" || echo "  !! set via UI: Tools & Settings > Server Settings > Full hostname"
grep -q "$HOSTNAME_FQDN" /etc/hosts 2>/dev/null || echo "$IPV4 $HOSTNAME_FQDN server" >> /etc/hosts

# 2) Components (do once via UI if not already installed) ----------------------
# Tools & Settings > Updates > Add/Remove Components: Mail server, Dovecot IMAP,
# Roundcube webmail, Fail2Ban, Let's Encrypt, SSL It!, WordPress Toolkit, PHP 8.3, Git.

# 3) Domains (both mail-ENABLED — the .dev forwarders receive mail) ------------
for D in "$DOMAIN_MAIN" "$DOMAIN_DEV"; do
  say "Subscription: $D"
  if $PLESK domain --info "$D" >/dev/null 2>&1; then echo "  exists — skip"; else
    $PLESK subscription --create "$D" -owner admin -service-plan "$SERVICE_PLAN" -ip "$IPV4" \
      || echo "  !! add via UI: Websites & Domains > Add Domain (mail ENABLED)"
  fi
done
$PLESK site-alias --create "www.$DOMAIN_MAIN" -domain "$DOMAIN_MAIN" >/dev/null 2>&1 || true
$PLESK site-alias --create "www.$DOMAIN_DEV"  -domain "$DOMAIN_DEV"  >/dev/null 2>&1 || true

# 4) Real mailboxes -----------------------------------------------------------
say "Create the 4 inboxes"
mkbox "cian@$DOMAIN_MAIN"    "$PW_CIAN"
mkbox "hello@$DOMAIN_MAIN"   "$PW_HELLO"
mkbox "admin@$DOMAIN_MAIN"   "$PW_ADMIN"
mkbox "website@$DOMAIN_MAIN" "$PW_WEBSITE"

# 5) Forwarders (see docs/deploy/mail-map.md) ---------------------------------
say "Create forwarders"
# -> cian@
mkfwd "dev@$DOMAIN_DEV" "cian@$DOMAIN_MAIN"
# -> hello@
for a in "contact@$DOMAIN_MAIN" "info@$DOMAIN_MAIN" "collab@$DOMAIN_MAIN" "projects@$DOMAIN_MAIN" \
         "hello@$DOMAIN_DEV" "projects@$DOMAIN_DEV"; do mkfwd "$a" "hello@$DOMAIN_MAIN"; done
# -> admin@
for a in "postmaster@$DOMAIN_MAIN" "abuse@$DOMAIN_MAIN" "security@$DOMAIN_MAIN" "hostmaster@$DOMAIN_MAIN" \
         "root@$DOMAIN_MAIN" "alerts@$DOMAIN_MAIN" "backups@$DOMAIN_MAIN" \
         "admin@$DOMAIN_DEV" "postmaster@$DOMAIN_DEV" "abuse@$DOMAIN_DEV" "security@$DOMAIN_DEV" \
         "hostmaster@$DOMAIN_DEV" "alerts@$DOMAIN_DEV"; do mkfwd "$a" "admin@$DOMAIN_MAIN"; done
# -> website@
for a in "webmaster@$DOMAIN_MAIN" "dmarc@$DOMAIN_MAIN" "notifications@$DOMAIN_MAIN" \
         "webmaster@$DOMAIN_DEV" "dmarc@$DOMAIN_DEV" "deploy@$DOMAIN_DEV" "bugs@$DOMAIN_DEV"; do mkfwd "$a" "website@$DOMAIN_MAIN"; done

# 6) DKIM on both domains -----------------------------------------------------
for D in "$DOMAIN_MAIN" "$DOMAIN_DEV"; do
  say "Enable DKIM on $D"
  $PLESK domain_pref --update "$D" -sign-outgoing-mail true 2>/dev/null \
    || $PLESK domain_pref -u "$D" -sign_outgoing_mail true 2>/dev/null \
    || echo "  !! enable via UI: $D > Mail Settings > 'Use DKIM to sign outgoing mail'"
done
say "DKIM records to add in Cloudflare (name: default._domainkey on each domain):"
for D in "$DOMAIN_MAIN" "$DOMAIN_DEV"; do
  echo "  -- $D --"; $PLESK dns --info "$D" 2>/dev/null | grep -i "_domainkey" \
    || echo "     (copy from: $D > Mail Settings > How to configure external DNS)"
done

# 7) SSL — run AFTER the Cloudflare records resolve (keep them grey-clouded) ---
say "Let's Encrypt (run when DNS is live):"
echo "  plesk bin extension --exec letsencrypt cli.php -d $DOMAIN_MAIN -d www.$DOMAIN_MAIN -d mail.$DOMAIN_MAIN -d webmail.$DOMAIN_MAIN -m $ADMIN_EMAIL"
echo "  plesk bin extension --exec letsencrypt cli.php -d $DOMAIN_DEV  -d www.$DOMAIN_DEV  -m $ADMIN_EMAIL"

say "Done. Review any '!!' lines, then: set reverse DNS (PTR) in IONOS, import the zone files into Cloudflare, and finish SSL per docs/INFRA.md."
