/*
 * Digital District — front-end interactions. Vanilla JS, no dependencies.
 * Everything here is progressive enhancement: the site is fully usable with
 * this file absent, and every motion respects prefers-reduced-motion and
 * touch devices.
 */
(function () {
  'use strict';

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var docEl = document.documentElement;

  docEl.classList.remove('no-js');

  /* ---- Reveal on scroll ---- */
  function initReveal() {
    var items = document.querySelectorAll('.reveal');
    if (!items.length) return;
    if (reduce || !('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-in'); });
      return;
    }
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            // Stagger siblings for a fluid cascade.
            var d = parseInt(e.target.getAttribute('data-delay') || '0', 10);
            e.target.style.transitionDelay = d + 'ms';
            e.target.classList.add('is-in');
            io.unobserve(e.target);
          }
        });
      },
      // threshold 0 (not a fraction): a very tall block — e.g. a long
      // case-study body — can never fit 12% of itself in the viewport, so a
      // fractional threshold would leave it stuck at opacity 0 forever. Reveal
      // as soon as any part crosses in; the -10% bottom margin keeps the feel.
      { rootMargin: '0px 0px -10% 0px', threshold: 0 }
    );
    items.forEach(function (el) { io.observe(el); });
  }

  /* ---- Count-up stats ---- */
  function initCounters() {
    var nums = document.querySelectorAll('[data-count]');
    if (!nums.length) return;
    if (reduce || !('IntersectionObserver' in window)) {
      nums.forEach(function (n) { n.textContent = n.getAttribute('data-count'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target;
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        var start = performance.now();
        var dur = 1100;
        function tick(now) {
          var p = Math.min((now - start) / dur, 1);
          var eased = 1 - Math.pow(1 - p, 3);
          el.textContent = String(Math.round(eased * target));
          if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
        io.unobserve(el);
      });
    }, { threshold: 0.4 });
    nums.forEach(function (n) { io.observe(n); });
  }

  /* ---- Custom cursor (dot + trailing ring) ---- */
  function initCursor() {
    if (!fine || reduce) return;
    var dot = document.createElement('div');
    var ring = document.createElement('div');
    dot.className = 'cursor-dot';
    ring.className = 'cursor-ring';
    document.body.appendChild(dot);
    document.body.appendChild(ring);
    document.body.classList.add('has-cursor');

    var mx = window.innerWidth / 2, my = window.innerHeight / 2;
    var rx = mx, ry = my;

    window.addEventListener('mousemove', function (e) {
      mx = e.clientX; my = e.clientY;
      dot.style.transform = 'translate3d(' + mx + 'px,' + my + 'px,0) translate(-50%,-50%)';
    }, { passive: true });

    function raf() {
      rx += (mx - rx) * 0.18;
      ry += (my - ry) * 0.18;
      ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0) translate(-50%,-50%)';
      requestAnimationFrame(raf);
    }
    raf();

    // Ring grows over interactive elements.
    var hot = 'a, button, .card, [data-hot], input, textarea';
    document.addEventListener('mouseover', function (e) {
      if (e.target.closest(hot)) ring.classList.add('is-hot');
    });
    document.addEventListener('mouseout', function (e) {
      if (e.target.closest(hot)) ring.classList.remove('is-hot');
    });
    document.addEventListener('mouseleave', function () {
      dot.style.opacity = ring.style.opacity = '0';
    });
    document.addEventListener('mouseenter', function () {
      dot.style.opacity = ring.style.opacity = '1';
    });
  }

  /* ---- Magnetic buttons ---- */
  function initMagnetic() {
    if (!fine || reduce) return;
    document.querySelectorAll('.magnetic').forEach(function (el) {
      var strength = 0.35;
      el.addEventListener('mousemove', function (e) {
        var r = el.getBoundingClientRect();
        var x = e.clientX - (r.left + r.width / 2);
        var y = e.clientY - (r.top + r.height / 2);
        el.style.transform = 'translate(' + x * strength + 'px,' + y * strength + 'px)';
      });
      el.addEventListener('mouseleave', function () {
        el.style.transform = '';
      });
    });
  }

  /* ---- Overlay / mobile menu ---- */
  function initMenu() {
    var overlay = document.querySelector('.overlay-menu');
    if (!overlay) return;
    var openers = document.querySelectorAll('[data-menu-open]');
    var closers = overlay.querySelectorAll('[data-menu-close]');
    var lastFocus = null;

    function focusable() { return overlay.querySelectorAll('a[href], button:not([disabled])'); }
    function open() {
      lastFocus = document.activeElement;
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      var f = focusable()[0]; if (f) f.focus();
    }
    function close() {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    openers.forEach(function (b) { b.addEventListener('click', open); });
    closers.forEach(function (b) { b.addEventListener('click', close); });
    overlay.querySelectorAll('.overlay-menu__list a').forEach(function (a) {
      a.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        var tag = (document.activeElement && document.activeElement.tagName) || '';
        if (!overlay.classList.contains('is-open')) {
          if (['INPUT', 'TEXTAREA', 'SELECT'].indexOf(tag) === -1) { e.preventDefault(); open(); }
        } else { close(); }
        return;
      }
      if (overlay.classList.contains('is-open') && e.key === 'Tab') {
        var nodes = focusable(); if (!nodes.length) return;
        var first = nodes[0], last = nodes[nodes.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  /* ---- Animated neon skyline hero (2D canvas, no dependency) ---- */
  function initHero() {
    var canvas = document.getElementById('hero-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    if (!ctx) return;
    var wrap = canvas.parentElement;
    var W = 0, H = 0, dpr = Math.min(window.devicePixelRatio || 1, 2);
    var cols = ['#7c3aed', '#ff2e88', '#22d3ee', '#a78bfa'];

    // Deterministic skyline of neon towers (back + front layers for parallax).
    var seed = 20260721;
    function rnd() { seed = (seed * 1664525 + 1013904223) % 4294967296; return seed / 4294967296; }
    var layers = [];
    [{ n: 26, s: 0.35, a: 0.5, base: 0.62 }, { n: 18, s: 0.7, a: 0.85, base: 0.82 }].forEach(function (cfg) {
      var b = [];
      for (var i = 0; i < cfg.n; i++) {
        b.push({ x: rnd(), w: 0.02 + rnd() * 0.05, h: 0.18 + rnd() * 0.5, c: cols[(rnd() * cols.length) | 0], lit: rnd() > 0.4 });
      }
      layers.push({ b: b, s: cfg.s, a: cfg.a, base: cfg.base });
    });

    var px = 0, py = 0, tpx = 0, tpy = 0;
    if (fine && !reduce) {
      window.addEventListener('mousemove', function (e) {
        tpx = (e.clientX / window.innerWidth - 0.5);
        tpy = (e.clientY / window.innerHeight - 0.5);
      }, { passive: true });
    }

    function resize() {
      W = wrap.clientWidth; H = wrap.clientHeight;
      canvas.width = W * dpr; canvas.height = H * dpr;
      canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function draw(t) {
      px += (tpx - px) * 0.05; py += (tpy - py) * 0.05;
      ctx.clearRect(0, 0, W, H);
      layers.forEach(function (L, li) {
        var shift = px * 40 * (li + 1);
        var vshift = py * 14 * (li + 1);
        L.b.forEach(function (tower) {
          var x = tower.x * W + shift;
          var w = tower.w * W;
          var h = tower.h * H;
          var y = H * L.base - vshift;
          ctx.globalAlpha = L.a;
          ctx.fillStyle = '#0b0d12';
          ctx.fillRect(x, y - h, w, h);
          // neon edge
          ctx.globalAlpha = L.a;
          ctx.strokeStyle = tower.c;
          ctx.lineWidth = 1;
          ctx.strokeRect(x + 0.5, y - h + 0.5, w - 1, h - 1);
          // lit windows / crown
          if (tower.lit) {
            var pulse = reduce ? 0.6 : 0.45 + Math.sin(t / 700 + tower.x * 10) * 0.25;
            ctx.globalAlpha = pulse * L.a;
            ctx.fillStyle = tower.c;
            ctx.fillRect(x + w * 0.3, y - h, w * 0.4, 3);
          }
        });
      });
      ctx.globalAlpha = 1;
    }

    resize();
    window.addEventListener('resize', resize);

    if (reduce) { draw(0); return; }
    var running = true;
    function loop(t) { if (!running) return; draw(t); requestAnimationFrame(loop); }
    var io = new IntersectionObserver(function (e) {
      running = e[0].isIntersecting;
      if (running) requestAnimationFrame(loop);
    }, { threshold: 0.02 });
    io.observe(canvas);
    requestAnimationFrame(loop);
  }

  /* ---- Atmosphere layers (scanline veil + hero aurora) ---- */
  function initFx() {
    if (!reduce) {
      var scan = document.createElement('div');
      scan.className = 'fx-scan';
      scan.setAttribute('aria-hidden', 'true');
      document.body.appendChild(scan);
    }
    var scene = document.querySelector('.hero__scene');
    if (scene) {
      var aur = document.createElement('div');
      aur.className = 'hero__aurora';
      aur.setAttribute('aria-hidden', 'true');
      scene.appendChild(aur);
    }
  }

  /* ---- Interactive cards: 3D tilt + cursor spotlight ---- */
  function initTilt() {
    if (!fine || reduce) return;
    document.querySelectorAll('.card').forEach(function (card) {
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width;
        var py = (e.clientY - r.top) / r.height;
        card.style.setProperty('--mx', (px * 100) + '%');
        card.style.setProperty('--my', (py * 100) + '%');
        card.style.transition = 'transform 80ms linear';
        card.style.transform =
          'perspective(900px) rotateY(' + (px - 0.5) * 6 + 'deg) rotateX(' + (0.5 - py) * 6 + 'deg) translateY(-4px)';
      });
      card.addEventListener('pointerleave', function () {
        card.style.transition = 'transform var(--dur-2) var(--ease)';
        card.style.transform = '';
      });
    });
  }

  /* ---- Text decode / scramble on the mono HUD readouts ---- */
  function initScramble() {
    if (reduce || !('IntersectionObserver' in window)) return;
    var glyphs = '0123456789<>-_/[]=+*#%ABCDEF';
    function scramble(el) {
      var text = el.textContent;
      // Only decode short, decorative readouts — longer informative labels
      // stay legible (never flash as garbled text).
      if (text.length > 24) return;
      var start = performance.now();
      var dur = 60 * text.length + 260;
      el.classList.add('scrambling');
      function frame(now) {
        var p = Math.min((now - start) / dur, 1);
        var reveal = Math.floor(p * text.length);
        var out = '';
        for (var i = 0; i < text.length; i++) {
          if (i < reveal || text[i] === ' ') out += text[i];
          else out += glyphs[(Math.random() * glyphs.length) | 0];
        }
        el.textContent = out;
        if (p < 1) requestAnimationFrame(frame);
        else { el.textContent = text; el.classList.remove('scrambling'); }
      }
      requestAnimationFrame(frame);
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { scramble(e.target); io.unobserve(e.target); }
      });
    }, { threshold: 0.6 });
    // Only text-only HUD labels (skip ones containing child elements, e.g. the
    // numbered section eyebrows, so their coloured markup is preserved).
    document.querySelectorAll('.hud').forEach(function (el) {
      if (el.childElementCount === 0 && el.textContent.trim()) io.observe(el);
    });
  }

  /* ---- Reading progress bar on article pages ---- */
  function initProgress() {
    var bar = document.querySelector('.read-progress span');
    var article = document.querySelector('.entry-content');
    if (!bar || !article) return;
    function update() {
      var rect = article.getBoundingClientRect();
      var total = rect.height - window.innerHeight;
      var scrolled = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
      bar.style.transform = 'scaleX(' + (total > 0 ? scrolled / total : 0) + ')';
    }
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFx();
    initProgress();
    initReveal();
    initCounters();
    initCursor();
    initMagnetic();
    initMenu();
    initHero();
    initTilt();
    initScramble();
  });
})();
