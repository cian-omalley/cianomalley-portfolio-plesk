import { identity, sections } from '@/data/site';

// System-status footer (Cyber Brutalism reference).
export default function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="py-12">
      <div className="mx-auto max-w-6xl px-5">
        <div className="grid gap-8 md:grid-cols-[1.4fr_1fr_1fr]">
          <div>
            <p className="flex items-center gap-3 font-display text-lg font-bold">
              <span className="h-3 w-3 rotate-45 bg-acid" aria-hidden />
              {identity.name}
            </p>
            <p className="mt-2 font-mono text-xs text-muted">
              {identity.role} — {identity.location}
            </p>
          </div>

          <nav aria-label="Footer" className="flex flex-col gap-2">
            {sections.map((s) => (
              <a
                key={s.id}
                href={`#${s.id}`}
                className="font-mono text-sm text-silver hover:text-acid"
              >
                {s.label}
              </a>
            ))}
          </nav>

          <div className="flex flex-col gap-2">
            {identity.social.map((s) => (
              <a
                key={s.href}
                href={s.href}
                rel="noopener"
                className="font-mono text-sm text-silver hover:text-acid"
              >
                {s.label} ↗
              </a>
            ))}
          </div>
        </div>

        <div className="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5 font-mono text-[11px] text-muted">
          <span>
            © {year} {identity.name}
          </span>
          <span className="flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-acid shadow-glow-acid" aria-hidden />
            SYS.ONLINE · Next.js + Three.js · static · Plesk
          </span>
        </div>
      </div>
    </footer>
  );
}
