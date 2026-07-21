// Single content source. Every fact here comes from the project's discovery
// record — no invented employers, degrees, clients, or skill percentages.
// Project statuses are honest (In Progress / Prototype / Research).

export type Status = 'In Progress' | 'Prototype' | 'Research' | 'Planned';

export interface Project {
  slug: string;
  title: string;
  tagline: string;
  status: Status;
  featured: boolean;
  tags: string[];
  summary: string;
}

export const identity = {
  name: "Cian O'Malley",
  handle: 'cian-omalley',
  role: 'Developer & Systems Builder',
  location: 'Stuttgart, Germany',
  coords: '48.7758° N, 9.1829° E',
  manifesto: "The future isn't magic. It's systematic.",
  positioning:
    'I build systems — self-hosted infrastructure, developer tooling, and interactive software — and document how they are made.',
  domains: { primary: 'cianomalley.works', showcase: 'cianomalley.dev' },
  social: [
    { label: 'GitHub', href: 'https://github.com/cian-omalley' },
    { label: 'cianomalley.dev', href: 'https://cianomalley.dev' },
  ],
} as const;

// Section anchors used by the nav + command menu.
export const sections = [
  { id: 'work', label: 'Work' },
  { id: 'principles', label: 'Principles' },
  { id: 'skills', label: 'Signal' },
  { id: 'log', label: 'Log' },
  { id: 'contact', label: 'Contact' },
] as const;

export const projects: Project[] = [
  {
    slug: 'ai-operating-system',
    title: 'AI Operating System',
    tagline: 'An agent-driven layer that orchestrates tools, memory, and tasks.',
    status: 'In Progress',
    featured: true,
    tags: ['AI', 'Agents', 'Systems'],
    summary:
      'A personal operating layer where AI agents coordinate tooling, long-term memory, and scheduled tasks. Architecture and core loops first; capabilities layered on.',
  },
  {
    slug: 'self-hosted-knowledge-hub',
    title: 'Self-Hosted Knowledge Hub',
    tagline: 'A private, searchable knowledge base on my own hardware.',
    status: 'In Progress',
    featured: true,
    tags: ['Self-Hosting', 'Search', 'Docker'],
    summary:
      'Notes, docs, and transcripts indexed and searchable, hosted on a home server behind Cloudflare — proving out the self-hosting stack the guides describe.',
  },
  {
    slug: 'interactive-developer-portfolio',
    title: 'Interactive Portfolio',
    tagline: "This site — a cyberpunk 'Digital District' rendered in the browser.",
    status: 'In Progress',
    featured: true,
    tags: ['Three.js', 'Next.js', 'Frontend'],
    summary:
      'The flagship case study: a progressively-enhanced portfolio with a real-time neon scene over an accessible baseline. This is the no-builder, open-source Plesk build.',
  },
  {
    slug: 'tactical-streaming-interface',
    title: 'Tactical Streaming Interface',
    tagline: 'An overlay and control surface for live streaming.',
    status: 'Prototype',
    featured: false,
    tags: ['Realtime', 'UI', 'Tooling'],
    summary:
      'A prototype control surface and overlay system for streaming workflows — exploring layout, hotkeys, and live data widgets.',
  },
  {
    slug: 'home-server-platform',
    title: 'Home Server Platform',
    tagline: 'The self-hosting foundation: Nginx + PHP + MariaDB + Redis + Cloudflare.',
    status: 'In Progress',
    featured: false,
    tags: ['Self-Hosting', 'Infra', 'Cloudflare'],
    summary:
      'The portable home-server stack everything else runs on, fronted by a Cloudflare Tunnel so the residential IP stays hidden.',
  },
  {
    slug: 'ai-research-workspace',
    title: 'AI Research Workspace',
    tagline: 'A workspace for running and comparing AI research experiments.',
    status: 'Research',
    featured: false,
    tags: ['AI', 'Research', 'Experiments'],
    summary:
      'An environment for structured AI experiments — tracking runs, prompts, and results. Early research stage.',
  },
];

// "Core principles" — how I work (Cyber Brutalism reference section).
export const principles = [
  {
    no: '01',
    title: 'Function Over Form',
    body: 'The accessible, server-rendered baseline ships first. Spectacle is an enhancement layered on top — never a requirement to read the content.',
  },
  {
    no: '02',
    title: 'Own The Stack',
    body: 'Self-hosted first, on a portable Linux stack. No lock-in to a builder or a platform I cannot move off in an afternoon.',
  },
  {
    no: '03',
    title: 'Document Everything',
    body: 'Every system worth building is worth a written guide. The writing ships before the video — and often before the system is “done”.',
  },
  {
    no: '04',
    title: 'Honest Status',
    body: 'Projects carry real states — In Progress, Prototype, Research. No invented results, no fabricated credentials, ever.',
  },
];

// Capability focus. These are self-declared emphasis areas — where effort goes —
// NOT proficiency scores or certifications. The bar values are relative focus.
export const capabilities = [
  {
    label: 'Self-Hosting & Infra',
    focus: 90,
    note: 'Nginx · PHP · MariaDB · Redis · Cloudflare',
  },
  { label: 'AI Systems & Agents', focus: 85, note: 'Orchestration · memory · tooling' },
  { label: 'Web & Interactive FE', focus: 80, note: 'Next.js · React · Three.js · a11y' },
  { label: 'Developer Tooling', focus: 75, note: 'JetBrains · editors · workflow' },
];

// Build log — honest project milestones, NOT employment history.
export const buildLog = [
  {
    tag: 'NOW',
    title: 'Building the portfolio in the open',
    body: 'Documenting this build as an Oxygen 6 tutorial series and review — the first real content for the channel.',
  },
  {
    tag: 'ACTIVE',
    title: 'Self-hosting stack on a home server',
    body: 'Standing up the portable Nginx/PHP/MariaDB/Redis stack behind a Cloudflare Tunnel.',
  },
  {
    tag: 'ACTIVE',
    title: 'AI Operating System — core loops',
    body: 'Agent orchestration, memory, and task scheduling under active development.',
  },
  {
    tag: 'NEXT',
    title: 'Guides & reviews going live',
    body: 'Self-hosting, Hermes Agent, WordPress and JetBrains guides; IDE reviews (IntelliJ, Antigravity, Codex).',
  },
];

export const guides = [
  { category: 'Self-Hosting', title: 'Self-Hosting from a Home Server' },
  { category: 'AI', title: 'Building with Hermes Agent' },
  { category: 'WordPress', title: 'WordPress Without a Page Builder' },
  { category: 'Development', title: 'A JetBrains-Centred Workflow' },
  { category: 'WordPress', title: 'Oxygen Builder: Field Notes' },
];

export const reviews = [
  { subject: 'Oxygen Builder 6', verdict: 'In Progress' },
  { subject: 'IntelliJ IDEA Ultimate', verdict: 'Planned' },
  { subject: 'Antigravity', verdict: 'Planned' },
  { subject: 'Codex', verdict: 'Planned' },
];

export const stack = [
  'Next.js',
  'React 19',
  'TypeScript',
  'Three.js',
  'Tailwind',
  'Framer Motion',
  'PHP 8.3',
  'WordPress',
  'MariaDB',
  'Redis',
  'Nginx',
  'Docker',
  'Cloudflare',
  'JetBrains',
];

// Stats are derived from the honest content above — no fabricated numbers.
export const stats = [
  { value: projects.length, suffix: '', label: 'Projects in flight' },
  {
    value: projects.filter((p) => p.featured).length,
    suffix: '',
    label: 'Flagship builds',
  },
  { value: guides.length, suffix: '', label: 'Guides in the works' },
  { value: stack.length, suffix: '', label: 'Tools in the stack' },
];
