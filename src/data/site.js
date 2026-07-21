// Single content source for the Plesk build. All facts here are drawn from
// the project's discovery record and concept docs — no invented employers,
// qualifications, clients, or achievements. Statuses are honest (nothing is
// claimed "live" that isn't). Edit this file to update the site; it is the
// vendor-neutral equivalent of the WordPress content model.

export const identity = {
  name: "Cian O'Malley",
  role: "Developer & Systems Builder",
  location: "Stuttgart, Germany",
  positioning:
    "I build systems — self-hosted infrastructure, developer tooling, and interactive software — and document how they're made.",
  domains: {
    primary: "cianomalley.works",
    showcase: "cianomalley.dev",
  },
  social: [
    { label: "GitHub", href: "https://github.com/cian-omalley" },
    { label: "cianomalley.dev", href: "https://cianomalley.dev" },
  ],
};

// The eight destinations of "The Digital District", mapped 1:1 to sections.
export const districts = [
  { id: "arrival",   short: "Home",     label: "Arrival Platform",     href: "/",          blurb: "Who I am and what I build." },
  { id: "projects",  short: "Projects", label: "Project Sector",       href: "/projects",  blurb: "Flagship builds and case studies." },
  { id: "guides",    short: "Guides",   label: "Knowledge Archive",    href: "/guides",    blurb: "Written guides and articles." },
  { id: "reviews",   short: "Reviews",  label: "Review Laboratory",    href: "/reviews",   blurb: "Software and developer-tool reviews." },
  { id: "about",     short: "About",    label: "Identity Chamber",     href: "/about",     blurb: "Focus, stack, and how I work." },
  { id: "lab",       short: "Lab",      label: "Experimental Sector",  href: "/lab",       blurb: "Prototypes and research experiments." },
  { id: "contact",   short: "Contact",  label: "Communications Relay", href: "/contact",   blurb: "Get in touch." },
];

// Six flagship project beacons (concept §1) + honest statuses.
export const projects = [
  {
    slug: "ai-operating-system",
    title: "AI Operating System",
    tagline: "An agent-driven layer that orchestrates tools, memory, and tasks.",
    status: "In Progress",
    statusClass: "status--progress",
    featured: true,
    tags: ["AI", "Agents", "Systems"],
    summary:
      "A personal operating layer where AI agents coordinate tooling, long-term memory, and scheduled tasks. In active development — architecture and core loops first, capabilities layered on.",
  },
  {
    slug: "self-hosted-knowledge-hub",
    title: "Self-Hosted Knowledge Hub",
    tagline: "A private, searchable knowledge base running on my own hardware.",
    status: "In Progress",
    statusClass: "status--progress",
    featured: true,
    tags: ["Self-Hosting", "Search", "Docker"],
    summary:
      "Notes, docs, and transcripts indexed and searchable, hosted entirely on a home server behind Cloudflare. Built to prove out the self-hosting stack the guides describe.",
  },
  {
    slug: "interactive-developer-portfolio",
    title: "Interactive Developer Portfolio",
    tagline: "This site — a cyberpunk 'Digital District' rendered in the browser.",
    status: "In Progress",
    statusClass: "status--progress",
    featured: true,
    tags: ["Three.js", "Astro", "Frontend"],
    summary:
      "The flagship case study: a progressively-enhanced portfolio with a lazy Three.js scene over a fully accessible baseline. This Plesk build is the no-builder, open-source variant.",
  },
  {
    slug: "tactical-streaming-interface",
    title: "Tactical Streaming Interface",
    tagline: "An overlay and control surface for live streaming.",
    status: "Prototype",
    statusClass: "status--prototype",
    featured: false,
    tags: ["Realtime", "UI", "Tooling"],
    summary:
      "A prototype control surface and overlay system for streaming workflows. Exploring layout, hotkeys, and live data widgets.",
  },
  {
    slug: "home-server-platform",
    title: "Home Server Platform",
    tagline: "The self-hosting foundation: Nginx + PHP + MariaDB + Redis + Cloudflare.",
    status: "In Progress",
    statusClass: "status--progress",
    featured: false,
    tags: ["Self-Hosting", "Infra", "Cloudflare"],
    summary:
      "The home-server platform everything else runs on — a portable stack (Nginx, PHP 8.3, MariaDB, Redis) fronted by a Cloudflare Tunnel so the residential IP stays hidden.",
  },
  {
    slug: "ai-research-workspace",
    title: "AI Research Workspace",
    tagline: "A workspace for running and comparing AI research experiments.",
    status: "Research",
    statusClass: "status--research",
    featured: false,
    tags: ["AI", "Research", "Experiments"],
    summary:
      "An environment for structured AI experiments — tracking runs, prompts, and results. Early research stage.",
  },
];

// Guide topics confirmed in discovery §5.
export const guides = [
  {
    slug: "self-hosting",
    category: "Self-Hosting",
    title: "Self-Hosting from a Home Server",
    excerpt:
      "Standing up a portable web stack at home — Nginx, PHP 8.3, MariaDB, Redis — behind a Cloudflare Tunnel, without exposing your IP.",
    status: "Planned",
  },
  {
    slug: "hermes-agent",
    category: "AI",
    title: "Building with Hermes Agent",
    excerpt: "Notes and patterns from working with the Hermes Agent tooling.",
    status: "Planned",
  },
  {
    slug: "wordpress-without-a-builder",
    category: "WordPress",
    title: "WordPress Without a Page Builder",
    excerpt:
      "How to run a fast, maintainable WordPress site using core tooling instead of a proprietary builder.",
    status: "Planned",
  },
  {
    slug: "jetbrains-workflow",
    category: "Development",
    title: "A JetBrains-Centred Workflow",
    excerpt: "Getting the most out of JetBrains IDEs for day-to-day development.",
    status: "Planned",
  },
  {
    slug: "oxygen-builder-notes",
    category: "WordPress",
    title: "Oxygen Builder: Field Notes",
    excerpt:
      "What the 'Building this portfolio with Oxygen 6' series covers — decisions, gotchas, and the architecture contract.",
    status: "Planned",
  },
];

// Review targets confirmed in discovery §5.
export const reviews = [
  {
    slug: "oxygen-builder-6",
    subject: "Oxygen Builder 6",
    category: "Software",
    verdict: "In Progress",
    excerpt:
      "A hands-on review written while building a real portfolio with it — the companion piece to the tutorial series.",
  },
  {
    slug: "intellij-idea-ultimate",
    subject: "IntelliJ IDEA Ultimate",
    category: "IDEs & Developer Tools",
    verdict: "Planned",
    excerpt: "The Ultimate edition for polyglot, full-stack development.",
  },
  {
    slug: "antigravity",
    subject: "Antigravity",
    category: "IDEs & Developer Tools",
    verdict: "Planned",
    excerpt: "First impressions of the Antigravity editor.",
  },
  {
    slug: "codex",
    subject: "Codex",
    category: "IDEs & Developer Tools",
    verdict: "Planned",
    excerpt: "Using Codex in a real development loop.",
  },
];

// Stack strip — server-rendered text, not decoration (concept §1.3).
export const stack = [
  "Astro", "Three.js", "TypeScript", "PHP 8.3", "WordPress",
  "MariaDB", "Redis", "Nginx", "Docker", "Cloudflare", "JetBrains",
];

// Focus areas for the Identity Chamber. Skills/focus only — no fabricated
// employment history. Fill timeline entries from real, supplied content.
export const focus = [
  { title: "Self-Hosting & Infrastructure", body: "Portable Linux web stacks, reverse proxies, tunnels, and backups — production-shaped, running at home first." },
  { title: "AI Systems & Agents", body: "Agent orchestration, memory, and tooling — the AI Operating System and research workspace." },
  { title: "Web & Interactive Frontend", body: "Accessible, progressively-enhanced interfaces with real-time 3D as an enhancement, never a requirement." },
  { title: "Developer Tooling", body: "Editors, IDEs, and workflow — reviewed and put to work, not just admired." },
];
