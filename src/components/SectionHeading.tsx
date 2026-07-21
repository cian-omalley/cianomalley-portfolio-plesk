import Reveal from './Reveal';

// Consistent brutalist section header: a numbered HUD tag + heading + optional
// lede. Server component — no interactivity of its own.
export default function SectionHeading({
  index,
  tag,
  title,
  lede,
}: {
  index: string;
  tag: string;
  title: string;
  lede?: string;
}) {
  return (
    <div className="max-w-2xl">
      <Reveal>
        <p className="hud">
          <span className="text-acid">{index}</span> &nbsp;/&nbsp; {tag}
        </p>
      </Reveal>
      <Reveal delay={0.05}>
        <h2 className="mt-4 font-display text-3xl font-bold tracking-tight sm:text-5xl">
          {title}
        </h2>
      </Reveal>
      {lede && (
        <Reveal delay={0.1}>
          <p className="mt-4 max-w-reading text-silver">{lede}</p>
        </Reveal>
      )}
    </div>
  );
}
