'use client';

import { motion, useReducedMotion } from 'framer-motion';
import { capabilities } from '@/data/site';
import SectionHeading from './SectionHeading';

// Focus "signal" bars. These show self-declared emphasis (where effort goes),
// NOT proficiency scores or credentials — labelled as focus, kept honest.
// Bars animate via scaleX (a transform) so there is no width-driven reflow.
export default function Capabilities() {
  const reduce = useReducedMotion();

  return (
    <section id="skills" className="scroll-mt-20 border-b border-line py-20">
      <div className="mx-auto max-w-6xl px-5">
        <SectionHeading
          index="03"
          tag="Signal / Focus"
          title="Where the effort goes"
          lede="Emphasis across the areas I build in — a map of focus, not a list of certifications."
        />

        <div className="mt-12 grid gap-8 md:grid-cols-2">
          {capabilities.map((c, i) => (
            <div key={c.label}>
              <div className="flex items-baseline justify-between">
                <span className="font-display text-lg font-bold">{c.label}</span>
                <span className="font-mono text-xs text-muted">{c.focus}%</span>
              </div>
              <div className="mt-3 h-2 w-full overflow-hidden bg-surface-2">
                <motion.div
                  className="h-full origin-left bg-gradient-to-r from-violet to-acid"
                  initial={reduce ? false : { scaleX: 0 }}
                  whileInView={{ scaleX: c.focus / 100 }}
                  viewport={{ once: true, margin: '0px 0px -15% 0px' }}
                  transition={{
                    duration: 0.9,
                    delay: i * 0.08,
                    ease: [0.22, 1, 0.36, 1],
                  }}
                  style={{ transformOrigin: 'left' }}
                />
              </div>
              <p className="mt-2 font-mono text-xs text-muted">{c.note}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
