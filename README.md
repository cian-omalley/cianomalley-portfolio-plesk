# Cian O'Malley — Portfolio (Plesk / no-builder edition)

A dynamic, animated **cyberpunk-brutalist** developer portfolio — "The Digital
District". Built with free and open-source tools and **no page builder** (no
Oxygen, no WordPress), then **statically exported** so it drops straight onto
**Plesk** hosting.

> This repository is a **separate fork**. The original WordPress/Oxygen build
> lives in `cian-omalley/cianomalley-portfolio` and is untouched.

## Stack — 100% free & open source

| Concern             | Tool                                                      | License    |
| ------------------- | --------------------------------------------------------- | ---------- |
| Framework / router  | [Next.js](https://nextjs.org) (App Router, static export) | MIT        |
| UI library          | [React 19](https://react.dev)                             | MIT        |
| Language            | TypeScript (strict)                                       | Apache-2.0 |
| Design system       | [Tailwind CSS](https://tailwindcss.com)                   | MIT        |
| UI animation        | [Framer Motion](https://www.framer.com/motion/)           | MIT        |
| Animated 3D hero    | [Three.js](https://threejs.org)                           | MIT        |
| Fonts (self-hosted) | Space Grotesk · Inter · JetBrains Mono (via `next/font`)  | OFL/MIT    |
| Contact backend     | Plain PHP (`public/contact.php`)                          | —          |

No proprietary builder, no SaaS form service, no external font/CDN calls, no trackers.

## Design

Interpreted from a reference board fusing three directions: **neon cyberpunk
cities**, **Cyber Brutalism** (mono type, acid-lime HUD, coordinates, system
status), and **dark personal-portfolio** layouts (stat counters, focus bars,
selected-work grid). The result: near-black canvas, electric-violet/magenta neon,
an acid-lime "systematic" accent, and an animated isometric neon city in the hero.

## What's in the box

- **Animated Three.js isometric neon city** in the hero — lazy, client-only,
  pauses off-screen, honours `prefers-reduced-motion`, and degrades to a CSS grid
  backdrop when WebGL is unavailable.
- **Framer Motion throughout** — scroll reveals, hover transforms, an infinite
  stack marquee, count-up stats, and `scaleX` focus bars (transform-only, no
  layout reflow).
- **Single-page portfolio** — Hero, stack marquee, stats, Selected Work, Core
  Principles, Signal/Focus, Build Log (+ guides/reviews), Contact — plus a themed
  404 and a keyboard-first command menu (opens with `Esc`, focus-trapped).
- **Accessibility** — semantic landmarks, skip link, visible focus rings,
  reduced-motion support, descriptive labels.
- **Design tokens centralised** in `tailwind.config.ts`; components never use raw
  hex (per `CLAUDE.md`).
- **Working PHP contact handler** with honeypot, validation, and mail-header
  hardening.
- **SEO** — metadata/Open Graph, `Person`/`WebSite` JSON-LD, `robots.txt`,
  `sitemap.xml`.

All content is drawn from the project's own discovery record — **no invented
biography, employers, degrees, or skill percentages**; every project carries an
honest status.

## Develop

```bash
pnpm install
pnpm dev         # http://localhost:3000
pnpm typecheck   # tsc --noEmit
pnpm lint        # next lint
pnpm format      # prettier --write .
pnpm build       # static export -> ./out  (+ contact.php)
```

Edit `src/data/site.ts` to change any content — it is the single content source.

## Deploy to Plesk

See **[DEPLOY-PLESK.md](DEPLOY-PLESK.md)**. In short: `pnpm build`, upload the
contents of `out/` to the domain's `httpdocs`, and set your address in
`contact.php` (or the `CIAN_CONTACT_TO` env var).

## License

[MIT](LICENSE).
