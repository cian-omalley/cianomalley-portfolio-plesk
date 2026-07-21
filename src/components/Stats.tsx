'use client';

import { useEffect, useRef, useState } from 'react';
import {
  animate,
  motion,
  useInView,
  useMotionValue,
  useReducedMotion,
} from 'framer-motion';
import { stats } from '@/data/site';

function Counter({ to, suffix }: { to: number; suffix: string }) {
  const ref = useRef<HTMLSpanElement>(null);
  const inView = useInView(ref, { once: true, margin: '0px 0px -20% 0px' });
  const reduce = useReducedMotion();
  const mv = useMotionValue(0);
  const [display, setDisplay] = useState(0);

  useEffect(() => {
    if (!inView) return;
    if (reduce) {
      setDisplay(to);
      return;
    }
    const controls = animate(mv, to, {
      duration: 1.1,
      ease: [0.22, 1, 0.36, 1],
      onUpdate: (v) => setDisplay(Math.round(v)),
    });
    return () => controls.stop();
  }, [inView, reduce, to, mv]);

  return (
    <span ref={ref} className="font-display text-5xl font-bold text-acid sm:text-6xl">
      {display}
      {suffix}
    </span>
  );
}

export default function Stats() {
  return (
    <section aria-label="By the numbers" className="border-b border-line">
      <div className="mx-auto grid max-w-6xl grid-cols-2 gap-px bg-line md:grid-cols-4">
        {stats.map((s) => (
          <motion.div
            key={s.label}
            className="bg-void px-5 py-10 text-center"
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.4 }}
          >
            <Counter to={s.value} suffix={s.suffix} />
            <p className="hud mt-3">{s.label}</p>
          </motion.div>
        ))}
      </div>
    </section>
  );
}
