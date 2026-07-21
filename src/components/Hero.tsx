'use client';

import dynamic from 'next/dynamic';
import { motion, useReducedMotion } from 'framer-motion';
import { identity } from '@/data/site';

// City scene is client-only and lazy — never blocks first paint of the copy.
const CityScene = dynamic(() => import('./CityScene'), {
  ssr: false,
  loading: () => null,
});

export default function Hero() {
  const reduce = useReducedMotion();
  const rise = (delay: number) =>
    reduce
      ? {}
      : {
          initial: { opacity: 0, y: 20 },
          animate: { opacity: 1, y: 0 },
          transition: { duration: 0.6, delay, ease: [0.22, 1, 0.36, 1] as const },
        };

  return (
    <section className="relative flex min-h-[92svh] items-center overflow-hidden border-b border-line">
      {/* Animated neon city + CSS fallback grid behind it. */}
      <div className="absolute inset-0" aria-hidden>
        <div className="absolute inset-0 -z-10 bg-grid opacity-40 [background-size:44px_44px]" />
        <div className="absolute inset-0 opacity-80">
          <CityScene />
        </div>
        {/* Legibility scrim over the scene. */}
        <div className="absolute inset-0 bg-gradient-to-r from-void via-void/70 to-transparent" />
        <div className="absolute inset-0 bg-gradient-to-t from-void via-transparent to-void/40" />
      </div>

      {/* Corner HUD readouts (Cyber Brutalism reference). */}
      <div className="pointer-events-none absolute inset-0 hidden md:block" aria-hidden>
        <span className="hud absolute left-5 top-24">{identity.coords}</span>
        <span className="hud absolute right-5 top-24 text-acid">{'// SYS.ONLINE'}</span>
        <span className="hud absolute bottom-5 right-5">BUILD 2.0 · STATIC · PLESK</span>
      </div>

      <div className="relative mx-auto w-full max-w-6xl px-5 py-24">
        <motion.p className="hud text-acid" {...rise(0)}>
          {identity.role} · {identity.location}
        </motion.p>

        <motion.h1
          className="mt-4 font-display text-5xl font-bold leading-[0.95] tracking-tight sm:text-7xl lg:text-8xl"
          {...rise(0.08)}
        >
          {identity.name.split(' ')[0]}
          <br />
          <span className="text-violet-soft">
            {identity.name.split(' ').slice(1).join(' ')}
          </span>
        </motion.h1>

        <motion.p
          className="mt-6 max-w-xl font-mono text-sm leading-relaxed text-silver"
          {...rise(0.16)}
        >
          <span className="text-acid">{identity.manifesto}</span> {identity.positioning}
        </motion.p>

        <motion.div className="mt-8 flex flex-wrap gap-3" {...rise(0.24)}>
          <a href="#work" className="btn btn-primary">
            View selected work →
          </a>
          <a href="#contact" className="btn btn-ghost">
            Open a channel
          </a>
        </motion.div>
      </div>

      <div className="hud absolute bottom-5 left-1/2 -translate-x-1/2">scroll ↓</div>
    </section>
  );
}
