import Nav from '@/components/Nav';
import Hero from '@/components/Hero';
import Marquee from '@/components/Marquee';
import Stats from '@/components/Stats';
import Work from '@/components/Work';
import Principles from '@/components/Principles';
import Capabilities from '@/components/Capabilities';
import BuildLog from '@/components/BuildLog';
import Contact from '@/components/Contact';
import Footer from '@/components/Footer';

export default function Home() {
  return (
    <>
      <Nav />
      <main id="main">
        <Hero />
        <Marquee />
        <Stats />
        <Work />
        <Principles />
        <Capabilities />
        <BuildLog />
        <Contact />
      </main>
      <Footer />
    </>
  );
}
