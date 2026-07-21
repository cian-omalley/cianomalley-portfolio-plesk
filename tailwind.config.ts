import type { Config } from 'tailwindcss';

// Central design system. Every color, font, and reusable spacing/animation
// token the site uses lives here — components reference these names, never raw
// hex values (see CLAUDE.md → Design System).
const config: Config = {
  content: ['./src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        // Surfaces
        void: '#05060A', // page background — near-black, faint blue
        surface: '#0B0D12', // panels / cards
        'surface-2': '#11141C', // raised panels
        line: '#1E222C', // hairline borders
        // Neon (from the cyberpunk-city references)
        violet: { DEFAULT: '#7C3AED', soft: '#A78BFA', deep: '#3A006F' },
        magenta: '#FF2E88', // neon pink signage
        cyan: '#22D3EE', // interactive / system signal
        // "Systematic" brutalist accent (Cyber Brutalism reference)
        acid: '#CCFF00',
        // Text
        ink: '#F5F7FA', // primary text
        silver: '#C8CDD8', // secondary text
        muted: '#7A8290', // metadata / labels
        warn: '#FF315B',
      },
      fontFamily: {
        display: ['var(--font-display)', 'ui-sans-serif', 'system-ui'],
        body: ['var(--font-body)', 'ui-sans-serif', 'system-ui'],
        mono: ['var(--font-mono)', 'ui-monospace', 'monospace'],
      },
      letterSpacing: { widest: '0.28em' },
      maxWidth: { reading: '70ch' },
      boxShadow: {
        'glow-violet': '0 0 32px rgba(124,58,237,0.28)',
        'glow-acid': '0 0 24px rgba(204,255,0,0.22)',
        'glow-cyan': '0 0 24px rgba(34,211,238,0.25)',
      },
      backgroundImage: {
        grid: 'linear-gradient(rgba(200,205,216,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(200,205,216,0.05) 1px, transparent 1px)',
      },
    },
  },
  plugins: [],
};

export default config;
