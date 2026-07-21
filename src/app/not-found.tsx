import type { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = { title: 'Signal lost — 404' };

export default function NotFound() {
  return (
    <main className="grid min-h-[70svh] place-items-center px-5 text-center">
      <div className="max-w-md">
        <p className="hud text-acid">Error 404</p>
        <h1 className="mt-3 font-display text-5xl font-bold">Signal lost</h1>
        <p className="mt-4 text-silver">
          That address isn&apos;t on the grid. The relay is still open, though.
        </p>
        <div className="mt-6 flex justify-center gap-3">
          <Link href="/" className="btn btn-primary">
            Return to base →
          </Link>
          <Link href="/#work" className="btn btn-ghost">
            Selected work
          </Link>
        </div>
      </div>
    </main>
  );
}
