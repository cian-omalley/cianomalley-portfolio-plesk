#!/usr/bin/env bash
# =============================================================================
# Plesk setup helper — Cian O'Malley portfolio   (PLACEHOLDER copy for the repo)
# -----------------------------------------------------------------------------
# Run as ROOT over SSH on the Debian + Plesk VPS:   bash plesk-setup.sh
#
# Safe to re-run: every step checks first and never deletes anything. Some
# `plesk bin` flags differ slightly between Plesk versions — if a step prints a
# "!!" line, do that one in the Plesk UI (the path is shown).
#
# Fill these in (your LOCAL copy already has the values; this repo copy uses
# placeholders so nothing sensitive is committed):
# -----------------------------------------------------------------------------
DOMAIN_MAIN="cianomalley.works"
DOMAIN_DEV="cianomalley.dev"
HOSTNAME_FQDN="server.cianomalley.works"
IPV4="<IPv4>"                 # IONOS > Network > Public IP
IPV6="<IPv6>"                 # leave "" if IPv6 isn't verified yet
SERVICE_PLAN="Unlimited"      # an existing Plesk Service Plan name (Service Plans list)
MAILBOX="info"                # creates info@cianomalley.works
MAILBOX_PASS="<STRONG-PASSWORD>"   # set your own; do not commit a real one
ADMIN_EMAIL="you@example.com"      # external address for Let's Encrypt / notices
# -----------------------------------------------------------------------------

PLESK="plesk bin"
say(){ printf '\n\033[1;36m== %s\033[0m\n' "$*"; }

# 1) Hostname (must be a full FQDN) -------------------------------------------
say "Set hostname -> $HOSTNAME_FQDN"
hostnamectl set-hostname "$HOSTNAME_FQDN" || echo "  !! set via UI: Tools & Settings > Server Settings > Full hostname"
grep -q "$HOSTNAME_FQDN" /etc/hosts 2>/dev/null || echo "$IPV4 $HOSTNAME_FQDN server" >> /etc/hosts

# 2) Components (mail, webmail, dkim, tooling) --------------------------------
# Easiest once via UI: Tools & Settings > Updates > Add/Remove Components
#   Mail server, Dovecot IMAP, Roundcube webmail, Fail2Ban, Let's Encrypt,
#   SSL It!, WordPress Toolkit, PHP 8.3, Git.
# CLI equivalent (uncomment to use):
# plesk installer --select-release-current \
#   --install-component roundcube --install-component fail2ban --install-component letsencrypt

# 3) Create the two domains (subscriptions) -----------------------------------
say "Subscription: $DOMAIN_MAIN"
if $PLESK domain --info "$DOMAIN_MAIN" >/dev/null 2>&1; then echo "  exists — skip"; else
  $PLESK subscription --create "$DOMAIN_MAIN" -owner admin -service-plan "$SERVICE_PLAN" -ip "$IPV4" \
    || echo "  !! add via UI: Websites & Domains > Add Domain (doc root httpdocs, mail ENABLED)"
fi
# www is an ALIAS of the main site (not its own domain):
$PLESK site-alias --create "www.$DOMAIN_MAIN" -domain "$DOMAIN_MAIN" >/dev/null 2>&1 || true

say "Subscription: $DOMAIN_DEV"
if $PLESK domain --info "$DOMAIN_DEV" >/dev/null 2>&1; then echo "  exists — skip"; else
  $PLESK subscription --create "$DOMAIN_DEV" -owner admin -service-plan "$SERVICE_PLAN" -ip "$IPV4" \
    || echo "  !! add via UI: Websites & Domains > Add Domain (mail DISABLED)"
fi

# 4) Mailbox ------------------------------------------------------------------
say "Mailbox: $MAILBOX@$DOMAIN_MAIN"
if $PLESK mail --info "$MAILBOX@$DOMAIN_MAIN" >/dev/null 2>&1; then echo "  exists — skip"; else
  $PLESK mail --create "$MAILBOX@$DOMAIN_MAIN" -mailbox true -passwd "$MAILBOX_PASS" -mbox-quota 2G \
    || echo "  !! create via UI: Mail > Create Email Address"
fi

# 5) Enable DKIM signing ------------------------------------------------------
say "Enable DKIM on $DOMAIN_MAIN"
$PLESK domain_pref --update "$DOMAIN_MAIN" -sign-outgoing-mail true 2>/dev/null \
  || $PLESK domain_pref -u "$DOMAIN_MAIN" -sign_outgoing_mail true 2>/dev/null \
  || echo "  !! enable via UI: $DOMAIN_MAIN > Mail Settings > 'Use DKIM to sign outgoing mail'"

# 6) Print the DKIM record for Cloudflare -------------------------------------
say "DKIM TXT to add in Cloudflare (name: default._domainkey):"
$PLESK dns --info "$DOMAIN_MAIN" 2>/dev/null | grep -i "_domainkey" \
  || echo "  (copy it from: $DOMAIN_MAIN > Mail Settings > How to configure external DNS)"

# 7) SSL — run ONLY after the Cloudflare records exist and resolve (grey-clouded)
say "Let's Encrypt (run after DNS is live):"
echo "  plesk bin extension --exec letsencrypt cli.php \\"
echo "    -d $DOMAIN_MAIN -d www.$DOMAIN_MAIN -d mail.$DOMAIN_MAIN -d webmail.$DOMAIN_MAIN -m $ADMIN_EMAIL"

say "Done. Handle any '!!' lines in the Plesk UI, then continue with the SSL + Cloudflare-proxy steps in docs/INFRA.md."
