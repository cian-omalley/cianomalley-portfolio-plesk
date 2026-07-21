// @ts-check
import { defineConfig } from 'astro/config';

// Static output — Astro builds to ./dist, which is uploaded to Plesk's
// document root (httpdocs). No Node runtime required on the server; the
// only server-side piece is the optional public/contact.php handler that
// Plesk's bundled PHP serves directly. No Oxygen, no WordPress, no builder.
export default defineConfig({
  site: 'https://cianomalley.works',
  output: 'static',
  build: {
    // Emit clean directory URLs (/projects/) so Plesk/Nginx serves index.html.
    format: 'directory',
  },
  trailingSlash: 'ignore',
});
