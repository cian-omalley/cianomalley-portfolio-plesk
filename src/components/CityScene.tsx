'use client';

import { useEffect, useRef } from 'react';
import * as THREE from 'three';

// An isometric neon mini-city (the board's signature look): emissive violet /
// magenta / cyan blocks on a dark plate, slowly rotating. It's a WebGL scene,
// not a UI transition, so it uses its own rAF loop; if WebGL is unavailable it
// renders nothing and the parent's CSS backdrop shows through. Pauses off-screen
// and honours prefers-reduced-motion (single static frame).
export default function CityScene() {
  const mountRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const mount = mountRef.current;
    if (!mount) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let renderer: THREE.WebGLRenderer;
    try {
      renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    } catch {
      return;
    }
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    mount.appendChild(renderer.domElement);
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    renderer.domElement.style.display = 'block';

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x05060a, 0.03);

    // Isometric-ish framing via a tight perspective camera set high and angled.
    const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 200);
    camera.position.set(24, 22, 24);
    camera.lookAt(0, 2, 0);

    scene.add(new THREE.AmbientLight(0x3a006f, 1.4));
    const key = new THREE.DirectionalLight(0x7c3aed, 2.4);
    key.position.set(10, 20, 6);
    scene.add(key);
    const fill = new THREE.DirectionalLight(0x22d3ee, 0.5);
    fill.position.set(-12, 8, -8);
    scene.add(fill);

    const city = new THREE.Group();
    scene.add(city);

    // Dark base plate with a neon rim.
    const plate = new THREE.Mesh(
      new THREE.BoxGeometry(26, 0.6, 26),
      new THREE.MeshStandardMaterial({ color: 0x0b0d12, roughness: 0.6, metalness: 0.4 }),
    );
    plate.position.y = -0.3;
    city.add(plate);

    // Deterministic block layout so the skyline is stable every load.
    let seed = 0x9e3779b9;
    const rand = () => {
      seed |= 0;
      seed = (seed + 0x6d2b79f5) | 0;
      let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };

    const neon = [0x7c3aed, 0xff2e88, 0x22d3ee, 0xa78bfa];
    const box = new THREE.BoxGeometry(1, 1, 1);
    const step = 2.1;

    for (let gx = -5; gx <= 5; gx++) {
      for (let gz = -5; gz <= 5; gz++) {
        if (rand() < 0.18) continue; // gaps = streets
        const h = 1.5 + rand() * (8 - Math.hypot(gx, gz) * 0.4);
        const height = Math.max(1, h);
        const color = neon[Math.floor(rand() * neon.length)];

        const building = new THREE.Mesh(
          box,
          new THREE.MeshStandardMaterial({
            color: 0x0d1016,
            roughness: 0.5,
            metalness: 0.5,
            emissive: color,
            emissiveIntensity: 0.35 + rand() * 0.5,
          }),
        );
        building.scale.set(1.4, height, 1.4);
        building.position.set(gx * step, height / 2, gz * step);
        city.add(building);

        // A bright neon "sign" strip on tall towers.
        if (height > 4 && rand() > 0.5) {
          const sign = new THREE.Mesh(
            new THREE.BoxGeometry(0.15, height * 0.5, 1.5),
            new THREE.MeshBasicMaterial({ color }),
          );
          sign.position.set(gx * step + 0.75, height * 0.6, gz * step);
          city.add(sign);
        }
      }
    }

    // Drifting particles for atmosphere.
    const dustGeo = new THREE.BufferGeometry();
    const dust = new Float32Array(240 * 3);
    for (let i = 0; i < 240; i++) {
      dust[i * 3] = (rand() - 0.5) * 30;
      dust[i * 3 + 1] = rand() * 16;
      dust[i * 3 + 2] = (rand() - 0.5) * 30;
    }
    dustGeo.setAttribute('position', new THREE.BufferAttribute(dust, 3));
    scene.add(
      new THREE.Points(
        dustGeo,
        new THREE.PointsMaterial({
          color: 0xc8cdd8,
          size: 0.06,
          transparent: true,
          opacity: 0.4,
        }),
      ),
    );

    function resize() {
      const w = mount!.clientWidth;
      const h = mount!.clientHeight;
      if (!w || !h) return;
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    let raf = 0;
    let running = !reduce;
    const clock = new THREE.Clock();

    const frame = () => {
      const t = clock.getElapsedTime();
      city.rotation.y = -0.35 + Math.sin(t * 0.06) * 0.25; // slow sway, never full spin
      renderer.render(scene, camera);
    };

    const loop = () => {
      if (!running) return;
      raf = requestAnimationFrame(loop);
      frame();
    };

    frame(); // always paint one frame (covers reduced-motion)

    const io = new IntersectionObserver(
      ([entry]) => {
        running = !reduce && entry.isIntersecting;
        if (running && !raf) loop();
        if (!running && raf) {
          cancelAnimationFrame(raf);
          raf = 0;
        }
      },
      { threshold: 0.05 },
    );
    io.observe(mount);

    return () => {
      io.disconnect();
      window.removeEventListener('resize', resize);
      cancelAnimationFrame(raf);
      renderer.dispose();
      if (renderer.domElement.parentNode === mount)
        mount.removeChild(renderer.domElement);
    };
  }, []);

  return <div ref={mountRef} className="h-full w-full" aria-hidden />;
}
