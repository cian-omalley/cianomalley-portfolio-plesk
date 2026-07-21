import type { Metadata, Viewport } from 'next';
import { Space_Grotesk, Inter, JetBrains_Mono } from 'next/font/google';
import { identity } from '@/data/site';
import './globals.css';

// Fonts are downloaded at build time and self-hosted by Next — no runtime
// calls to Google, which keeps the static export private and offline-friendly.
const display = Space_Grotesk({
  subsets: ['latin'],
  weight: ['500', '700'],
  variable: '--font-display',
});
const body = Inter({ subsets: ['latin'], variable: '--font-body' });
const mono = JetBrains_Mono({ subsets: ['latin'], variable: '--font-mono' });

const url = `https://${identity.domains.primary}`;

export const metadata: Metadata = {
  metadataBase: new URL(url),
  title: `${identity.name} — ${identity.role}`,
  description: identity.positioning,
  openGraph: {
    title: `${identity.name} — ${identity.role}`,
    description: identity.positioning,
    url,
    siteName: identity.name,
    type: 'website',
  },
  twitter: { card: 'summary_large_image' },
  robots: { index: true, follow: true },
  icons: { icon: '/favicon.svg' },
};

export const viewport: Viewport = {
  themeColor: '#05060A',
};

const personJsonLd = {
  '@context': 'https://schema.org',
  '@type': 'Person',
  name: identity.name,
  jobTitle: identity.role,
  address: {
    '@type': 'PostalAddress',
    addressLocality: 'Stuttgart',
    addressCountry: 'DE',
  },
  url,
  sameAs: identity.social.map((s) => s.href),
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${display.variable} ${body.variable} ${mono.variable}`}>
      <body>
        <a
          href="#main"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-acid focus:px-4 focus:py-2 focus:font-mono focus:text-void"
        >
          Skip to content
        </a>
        {children}
        <script
          type="application/ld+json"
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{ __html: JSON.stringify(personJsonLd) }}
        />
      </body>
    </html>
  );
}
