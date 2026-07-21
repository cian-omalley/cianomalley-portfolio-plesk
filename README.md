# Cian O'Malley — Portfolio (Plesk / no-builder edition)

A dynamic, animated cyberpunk developer portfolio — **"The Digital District"** — built
with free and open-source tools and **no page builder** (no Oxygen, no WordPress). This is
a standalone fork of the original WordPress/Oxygen concept, re-implemented so it can be
hosted on **Plesk** (or any static host) with almost nothing to maintain.

> This repository is a **separate fork**. The original WordPress build lives in
> `cian-omalley/cianomalley-portfolio` and is untouched.

## Stack — 100% free & open source

| Concern | Tool | License |
|---|---|---|
| Site framework / "builder" | [Astro](https://astro.build) | MIT |
| Animated 3D hero | [Three.js](https://threejs.org) | MIT |
| Self-hosted fonts | [Fontsource](https://fontsource.org) (Inter, Space Grotesk, JetBrains Mono) | MIT / OFL |
| Scroll + menu interaction | Vanilla JS (IntersectionObserver) | — |
| Contact form backend | Plain PHP (`public/contact.php`) | — |

No proprietary builder, no SaaS form service, no external font/CDN calls, no trackers.

## What's in the box

- **Animated Three.js "dense mini city"** hero — camera at street level, edges hidden by
  fog + a surrounding skyline shell (per the original design brief). Lazy, pauses when
  off-screen, and falls back to a pure-CSS cityscape when WebGL/JS is unavailable.
- **Progressive enhancement throughout** — the site is fully readable with JavaScript off.
  Reveal-on-scroll, parallax, and the 3D scene are all enhancements.
- **Accessible baseline** — skip link, visible focus rings, keyboard-operable command menu
  (opens with `Esc`), `prefers-reduced-motion` respected everywhere, semantic landmarks.
- **Seven pages** — Arrival (home), Project Sector, Knowledge Archive (guides), Review
  Laboratory, Identity Chamber (about), Experimental Sector (lab), Communications Relay
  (contact) — plus a themed 404.
- **Design tokens ported verbatim** from the original plugin so the look matches the
  WordPress concept exactly (`src/styles/tokens.css`).
- **Working PHP contact handler** with honeypot, validation, and mail-header hardening.
- **SEO basics** — per-page titles/descriptions, Open Graph, `Person`/`WebSite` JSON-LD,
  `robots.txt`, `sitemap.xml`.

All content is drawn from the project's own discovery record — **no invented biography**,
and every project carries an honest status (In Progress / Prototype / Research).

## Develop

```bash
npm install
npm run dev      # http://localhost:4321
npm run build    # -> ./dist  (static site + contact.php)
npm run preview  # serve the production build locally
```

Edit `src/data/site.js` to change any content — it is the single content source.

## Deploy to Plesk

See **[DEPLOY-PLESK.md](DEPLOY-PLESK.md)** for the full walkthrough. In short: run
`npm run build`, upload the contents of `dist/` to the domain's document root
(`httpdocs`), and set your address in `contact.php` (or the `CIAN_CONTACT_TO` env var).

## License

[MIT](LICENSE).
