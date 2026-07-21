'use client';

import { useEffect, useRef, useState } from 'react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import { identity, sections } from '@/data/site';

// Sticky HUD nav + a keyboard-first command overlay (opens on Esc, closes on
// Esc/backdrop, traps focus, returns focus to the trigger).
export default function Nav() {
  const [open, setOpen] = useState(false);
  const reduce = useReducedMotion();
  const triggerRef = useRef<HTMLButtonElement>(null);
  const panelRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if (e.key !== 'Escape') return;
      const tag = (document.activeElement?.tagName ?? '').toUpperCase();
      if (!open && ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;
      e.preventDefault();
      setOpen((v) => !v);
    }
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open]);

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
      panelRef.current?.querySelector<HTMLElement>('a,button')?.focus();
    } else {
      triggerRef.current?.focus();
    }
  }, [open]);

  return (
    <>
      <header className="sticky top-0 z-30 border-b border-line bg-void/70 backdrop-blur-md">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-5 py-3">
          <a href="#main" className="flex items-center gap-3 font-display font-bold">
            <span className="h-3 w-3 rotate-45 bg-acid shadow-glow-acid" aria-hidden />
            <span>{identity.name}</span>
          </a>

          <nav aria-label="Primary" className="hidden items-center gap-6 md:flex">
            {sections.map((s) => (
              <a
                key={s.id}
                href={`#${s.id}`}
                className="font-mono text-xs uppercase tracking-widest text-silver transition-colors hover:text-acid"
              >
                {s.label}
              </a>
            ))}
          </nav>

          <button
            ref={triggerRef}
            type="button"
            onClick={() => setOpen(true)}
            aria-haspopup="dialog"
            aria-expanded={open}
            className="btn btn-ghost !px-3 !py-2 text-xs"
          >
            Menu
            <kbd className="rounded border border-line px-1.5 py-0.5 text-[10px] text-muted">
              Esc
            </kbd>
          </button>
        </div>
      </header>

      <AnimatePresence>
        {open && (
          <motion.div
            className="fixed inset-0 z-40 grid place-items-center p-4"
            role="dialog"
            aria-modal="true"
            aria-label="System menu"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: reduce ? 0 : 0.2 }}
          >
            <button
              type="button"
              aria-label="Close menu"
              className="absolute inset-0 bg-void/80 backdrop-blur-sm"
              onClick={() => setOpen(false)}
            />
            <motion.div
              ref={panelRef}
              className="panel relative w-full max-w-lg p-6"
              initial={reduce ? false : { opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              exit={reduce ? undefined : { opacity: 0, y: 16 }}
              transition={{ duration: reduce ? 0 : 0.2 }}
              onKeyDown={(e) => {
                if (e.key !== 'Tab') return;
                const nodes = panelRef.current?.querySelectorAll<HTMLElement>('a,button');
                if (!nodes || nodes.length === 0) return;
                const first = nodes[0];
                const last = nodes[nodes.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                  e.preventDefault();
                  last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                  e.preventDefault();
                  first.focus();
                }
              }}
            >
              <div className="flex items-center justify-between">
                <p className="hud">System menu</p>
                <button
                  type="button"
                  onClick={() => setOpen(false)}
                  aria-label="Close menu"
                  className="text-silver hover:text-acid"
                >
                  ✕
                </button>
              </div>
              <div className="mt-5 grid gap-2 sm:grid-cols-2">
                {sections.map((s) => (
                  <a
                    key={s.id}
                    href={`#${s.id}`}
                    onClick={() => setOpen(false)}
                    className="border border-line bg-surface-2/60 px-4 py-3 font-display transition-colors hover:border-acid"
                  >
                    {s.label}
                  </a>
                ))}
              </div>
              <div className="mt-5 flex flex-wrap gap-4 border-t border-line pt-4 font-mono text-xs">
                {identity.social.map((s) => (
                  <a
                    key={s.href}
                    href={s.href}
                    rel="noopener"
                    className="text-cyan hover:text-acid"
                  >
                    {s.label}
                  </a>
                ))}
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
