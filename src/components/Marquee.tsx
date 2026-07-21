'use client';

import { motion, useReducedMotion } from 'framer-motion';
import { stack } from '@/data/site';

// Infinite horizontal marquee of the stack. Two copies of the list slide by
// -50% so the loop is seamless; reduced-motion shows a static, wrapped row.
export default function Marquee() {
  const reduce = useReducedMotion();
  const row = [...stack, ...stack];

  return (
    <div
      className="border-y border-line bg-surface/40 py-4"
      aria-label="Technology stack"
    >
      {reduce ? (
        <ul className="mx-auto flex max-w-6xl flex-wrap justify-center gap-x-8 gap-y-2 px-5">
          {stack.map((t) => (
            <li key={t} className="font-mono text-sm text-muted">
              {t}
            </li>
          ))}
        </ul>
      ) : (
        <div className="overflow-hidden">
          <motion.ul
            className="flex w-max gap-10"
            animate={{ x: ['0%', '-50%'] }}
            transition={{ duration: 30, ease: 'linear', repeat: Infinity }}
          >
            {row.map((t, i) => (
              <li
                key={`${t}-${i}`}
                className="flex items-center gap-10 font-mono text-sm text-muted"
              >
                <span className="text-acid">/</span>
                {t}
              </li>
            ))}
          </motion.ul>
        </div>
      )}
    </div>
  );
}
