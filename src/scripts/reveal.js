// Reveal-on-scroll — progressive enhancement only. Elements with [data-reveal]
// start visible in the HTML; we opt them into the animated state at runtime so
// no-JS and reduced-motion users always see full content.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const items = document.querySelectorAll('[data-reveal]');

if (!items.length) {
  // nothing to do
} else if (reduce || !('IntersectionObserver' in window)) {
  items.forEach((el) => el.classList.add('reveal', 'is-visible'));
} else {
  items.forEach((el) => el.classList.add('reveal'));
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    },
    { rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
  );
  items.forEach((el) => io.observe(el));
}
