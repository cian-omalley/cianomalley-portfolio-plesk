// Command menu: open via [data-open-command] or the Esc key, close via
// backdrop / close button / Esc. Focus is trapped while open and returned to
// the trigger on close. Pure DOM, keyboard-first.
const menu = document.getElementById('command-menu');
if (menu) {
  const openers = document.querySelectorAll('[data-open-command]');
  const closers = menu.querySelectorAll('[data-close-command]');
  let lastFocused = null;

  const focusable = () =>
    menu.querySelectorAll('a[href], button:not([disabled])');

  const open = () => {
    if (!menu.hidden) return;
    lastFocused = document.activeElement;
    menu.hidden = false;
    document.body.style.overflow = 'hidden';
    const first = focusable()[0];
    if (first) first.focus();
  };

  const close = () => {
    if (menu.hidden) return;
    menu.hidden = true;
    document.body.style.overflow = '';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  };

  openers.forEach((btn) => btn.addEventListener('click', open));
  closers.forEach((el) => el.addEventListener('click', close));

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (menu.hidden) {
        // Esc anywhere opens the menu (concept §1.5), unless a field is focused.
        const tag = (document.activeElement && document.activeElement.tagName) || '';
        if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) {
          e.preventDefault();
          open();
        }
      } else {
        close();
      }
      return;
    }

    if (!menu.hidden && e.key === 'Tab') {
      const nodes = Array.from(focusable());
      if (!nodes.length) return;
      const first = nodes[0];
      const last = nodes[nodes.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });
}
