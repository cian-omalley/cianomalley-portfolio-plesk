import { principles } from '@/data/site';
import Reveal from './Reveal';
import SectionHeading from './SectionHeading';

// "Core principles" — the Cyber Brutalism reference's how-I-work grid.
export default function Principles() {
  return (
    <section id="principles" className="scroll-mt-20 border-b border-line py-20">
      <div className="mx-auto max-w-6xl px-5">
        <SectionHeading
          index="02"
          tag="Core Principles"
          title="How the work gets built"
          lede="The future isn't minimal — it's systematic. A few non-negotiables behind every project."
        />

        <div className="mt-12 grid gap-px bg-line md:grid-cols-2">
          {principles.map((p, i) => (
            <Reveal key={p.no} delay={(i % 2) * 0.06} as="div">
              <div className="h-full bg-void p-6">
                <p className="font-display text-4xl font-bold text-violet-soft">{p.no}</p>
                <h3 className="mt-3 font-display text-xl font-bold">{p.title}</h3>
                <p className="mt-2 text-sm text-silver">{p.body}</p>
              </div>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
