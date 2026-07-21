import { buildLog, guides, reviews } from '@/data/site';
import Reveal from './Reveal';
import SectionHeading from './SectionHeading';

const tagColor: Record<string, string> = {
  NOW: 'text-acid',
  ACTIVE: 'text-cyan',
  NEXT: 'text-violet-soft',
};

// Build log = honest project milestones and what's next. Deliberately NOT an
// employment/education timeline — no roles or degrees are fabricated.
export default function BuildLog() {
  return (
    <section id="log" className="scroll-mt-20 border-b border-line py-20">
      <div className="mx-auto max-w-6xl px-5">
        <SectionHeading
          index="04"
          tag="Build Log"
          title="Current trajectory"
          lede="What's being built right now, and what's queued next. Milestones, not a résumé."
        />

        <ol className="mt-12 border-l border-line">
          {buildLog.map((e, i) => (
            <Reveal key={e.title} delay={i * 0.05} as="li">
              <div className="relative pb-8 pl-6">
                <span className="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full bg-acid shadow-glow-acid" />
                <span
                  className={`font-mono text-xs uppercase tracking-widest ${tagColor[e.tag] ?? 'text-muted'}`}
                >
                  {e.tag}
                </span>
                <h3 className="mt-1 font-display text-lg font-bold">{e.title}</h3>
                <p className="mt-1 max-w-reading text-sm text-silver">{e.body}</p>
              </div>
            </Reveal>
          ))}
        </ol>

        <div className="mt-8 grid gap-px bg-line md:grid-cols-2">
          <Reveal as="div">
            <div className="h-full bg-void p-6">
              <p className="hud">Knowledge Archive · guides</p>
              <ul className="mt-4 space-y-3">
                {guides.map((g) => (
                  <li key={g.title} className="flex items-start gap-3 text-sm">
                    <span className="mt-0.5 font-mono text-[11px] text-cyan">
                      {g.category}
                    </span>
                    <span className="text-silver">{g.title}</span>
                  </li>
                ))}
              </ul>
            </div>
          </Reveal>

          <Reveal as="div" delay={0.06}>
            <div className="h-full bg-void p-6">
              <p className="hud">Review Laboratory · reviews</p>
              <ul className="mt-4 space-y-3">
                {reviews.map((r) => (
                  <li
                    key={r.subject}
                    className="flex items-center justify-between text-sm"
                  >
                    <span className="text-silver">{r.subject}</span>
                    <span className="font-mono text-[11px] text-muted">{r.verdict}</span>
                  </li>
                ))}
              </ul>
            </div>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
