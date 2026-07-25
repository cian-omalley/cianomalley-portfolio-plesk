#!/usr/bin/env bash
# Package the theme into digital-district.zip, ready to upload to WordPress
# (Appearance → Themes → Add New → Upload Theme) or Plesk WP Toolkit.
#
# Usage:  ./package.sh
set -euo pipefail

cd "$(dirname "$0")"
OUT="digital-district.zip"
rm -f "$OUT"

# Zip the theme folder itself (so it extracts to wp-content/themes/digital-district).
# Exclude junk that should never ship.
zip -r "$OUT" digital-district \
  -x '*/.DS_Store' -x '*/Thumbs.db' -x '*/.git/*' >/dev/null

echo "Built $OUT ($(du -h "$OUT" | cut -f1))"
echo "Upload it via wp-admin → Appearance → Themes → Add New → Upload Theme, then Activate."
