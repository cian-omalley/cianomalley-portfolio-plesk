'use client';

import { identity } from '@/data/site';
import Reveal from './Reveal';
import SectionHeading from './SectionHeading';

// Posts to /contact.php (Plesk's bundled PHP). Native HTML validation; no
// third-party form service, no tracking. The honeypot field mirrors the PHP.
export default function Contact() {
  return (
    <section id="contact" className="scroll-mt-20 border-b border-line py-20">
      <div className="mx-auto max-w-6xl px-5">
        <SectionHeading
          index="05"
          tag="Communications Relay"
          title="Open a channel"
          lede="Working on something, hiring, or want to compare notes on self-hosting? Send a signal."
        />

        <div className="mt-12 grid gap-8 lg:grid-cols-[1.5fr_1fr]">
          <Reveal as="div">
            <form action="/contact.php" method="post" className="panel grid gap-4 p-6">
              <label className="grid gap-2">
                <span className="hud">Name</span>
                <input
                  name="name"
                  type="text"
                  required
                  maxLength={120}
                  autoComplete="name"
                  className="border border-line bg-void px-3 py-2.5 font-body text-ink outline-none focus:border-cyan"
                />
              </label>
              <label className="grid gap-2">
                <span className="hud">Email</span>
                <input
                  name="email"
                  type="email"
                  required
                  maxLength={180}
                  autoComplete="email"
                  className="border border-line bg-void px-3 py-2.5 font-body text-ink outline-none focus:border-cyan"
                />
              </label>
              <label className="grid gap-2">
                <span className="hud">Message</span>
                <textarea
                  name="message"
                  required
                  maxLength={4000}
                  rows={6}
                  className="border border-line bg-void px-3 py-2.5 font-body text-ink outline-none focus:border-cyan"
                />
              </label>
              {/* Honeypot — hidden from users, catches bots. Mirrors contact.php. */}
              <input
                type="text"
                name="company"
                tabIndex={-1}
                autoComplete="off"
                aria-hidden
                className="absolute left-[-9999px] h-px w-px"
              />
              <div className="flex items-center gap-4">
                <button type="submit" className="btn btn-primary">
                  Transmit →
                </button>
                <span className="font-mono text-xs text-muted">
                  or use a direct channel
                </span>
              </div>
            </form>
          </Reveal>

          <Reveal as="div" delay={0.06}>
            <div className="panel h-full p-6">
              <p className="hud">Direct channels</p>
              <ul className="mt-4 space-y-2">
                {identity.social.map((s) => (
                  <li key={s.href}>
                    <a
                      href={s.href}
                      rel="noopener"
                      className="text-silver hover:text-acid"
                    >
                      {s.label} ↗
                    </a>
                  </li>
                ))}
              </ul>
              <p className="mt-6 font-mono text-xs text-muted">{identity.coords}</p>
              <p className="font-mono text-xs text-muted">{identity.location}</p>
              <p className="mt-6 font-mono text-[11px] leading-relaxed text-muted">
                No mail configured yet? Wire{' '}
                <code className="text-violet-soft">public/contact.php</code> to your
                mailbox on Plesk to receive messages.
              </p>
            </div>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
