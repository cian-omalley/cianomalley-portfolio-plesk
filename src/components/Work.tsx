'use client';

import { motion, useReducedMotion } from 'framer-motion';
import { projects, type Status } from '@/data/site';
import SectionHeading from './SectionHeading';

const statusColor: Record<Status, string> = {
  'In Progress': 'text-cyan',
  Prototype: 'text-violet-soft',
  Research: 'text-silver',
  Planned: 'text-muted',
};

export default function Work() {
  const reduce = useReducedMotion();

  return (
    <section id="work" className="scroll-mt-20 border-b border-line py-20">
      <div className="mx-auto max-w-6xl px-5">
        <SectionHeading
          index="01"
          tag="Selected Work"
          title="Systems in flight"
          lede="Flagship builds entered with honest statuses. No invented results — everything here is genuinely in progress, prototype, or research."
        />

        <div className="mt-12 grid gap-px bg-line sm:grid-cols-2 lg:grid-cols-3">
          {projects.map((p, i) => (
            <motion.article
              key={p.slug}
              className="group relative flex flex-col gap-3 bg-void p-6"
              initial={reduce ? false : { opacity: 0, y: 20 }}
              whileInView={reduce ? undefined : { opacity: 1, y: 0 }}
              viewport={{ once: true, margin: '0px 0px -8% 0px' }}
              transition={{ duration: 0.45, delay: (i % 3) * 0.06 }}
              whileHover={reduce ? undefined : { y: -4 }}
            >
              <div className="flex items-center justify-between">
                <span
                  className={`font-mono text-xs uppercase tracking-widest ${statusColor[p.status]}`}
                >
                  ● {p.status}
                </span>
                <span className="font-mono text-xs text-muted">
                  {String(i + 1).padStart(2, '0')}
                </span>
              </div>

              <h3 className="font-display text-xl font-bold">{p.title}</h3>
              <p className="text-sm text-silver">{p.tagline}</p>
              <p className="text-sm text-muted">{p.summary}</p>

              <ul className="mt-auto flex flex-wrap gap-2 pt-3">
                {p.tags.map((t) => (
                  <li
                    key={t}
                    className="border border-line px-2 py-1 font-mono text-[11px] text-muted"
                  >
                    {t}
                  </li>
                ))}
              </ul>

              {/* Acid underline sweeps in on hover — transform only, no reflow. */}
              <span className="absolute inset-x-0 bottom-0 h-0.5 origin-left scale-x-0 bg-acid transition-transform duration-300 group-hover:scale-x-100" />
            </motion.article>
          ))}
        </div>
      </div>
    </section>
  );
}
