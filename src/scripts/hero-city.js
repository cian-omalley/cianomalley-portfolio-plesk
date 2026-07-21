// The Digital District — one lazy Three.js scene. A compact, DENSE mini city
// with the camera at street level and a surrounding skyline shell + fog so the
// edges are never visible (discovery §6). Progressive enhancement: if WebGL
// or the module fails, the CSS fallback in Hero3D.astro stays on screen.
import * as THREE from 'three';

export function initDistrict(canvasId) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  } catch (err) {
    return; // no WebGL — CSS fallback remains
  }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setClearColor(0x000000, 0);

  const hero = canvas.closest('.hero');
  if (hero) hero.classList.add('is-webgl');

  // --- Palette (design tokens) ---
  const VOID = 0x040805;
  const PURPLE_DEEP = 0x3a006f;
  const VIOLET = 0x7c3aed;
  const SILVER = 0xc8cdd8;
  const CYAN = 0x22d3ee;

  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(VOID, 0.055); // fog hides the world edges

  const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 200);
  camera.position.set(0, 2.4, 12); // street level, inside the city
  camera.lookAt(0, 3, -8);

  // --- Lights ---
  scene.add(new THREE.AmbientLight(PURPLE_DEEP, 1.1));
  const key = new THREE.DirectionalLight(VIOLET, 2.2);
  key.position.set(6, 14, 8);
  scene.add(key);
  const rim = new THREE.DirectionalLight(SILVER, 0.6);
  rim.position.set(-8, 6, -10);
  scene.add(rim);

  // --- Ground ---
  const ground = new THREE.Mesh(
    new THREE.PlaneGeometry(240, 240),
    new THREE.MeshStandardMaterial({ color: 0x0b1010, roughness: 0.95, metalness: 0.1 })
  );
  ground.rotation.x = -Math.PI / 2;
  ground.position.y = -0.01;
  scene.add(ground);

  // --- Dense building field via instancing (cheap, many blocks) ---
  const COUNT = 220;
  const box = new THREE.BoxGeometry(1, 1, 1);
  const buildingMat = new THREE.MeshStandardMaterial({
    color: 0x0d1414, roughness: 0.55, metalness: 0.35,
    emissive: VIOLET, emissiveIntensity: 0.04,
  });
  const buildings = new THREE.InstancedMesh(box, buildingMat, COUNT);
  const dummy = new THREE.Object3D();
  const rand = mulberry32(20240716); // deterministic layout

  let placed = 0;
  for (let i = 0; placed < COUNT && i < COUNT * 4; i++) {
    // grid-ish scatter around the camera, leaving a central plaza clear
    const gx = (rand() - 0.5) * 44;
    const gz = (rand() - 0.5) * 44 - 6;
    const distToPlaza = Math.hypot(gx, gz + 4);
    if (distToPlaza < 5) continue; // keep the Arrival Platform plaza open
    const h = 2 + rand() * (distToPlaza > 16 ? 16 : 9); // taller shell outside
    const w = 1.4 + rand() * 2.2;
    const d = 1.4 + rand() * 2.2;
    dummy.position.set(gx, h / 2, gz);
    dummy.scale.set(w, h, d);
    dummy.rotation.y = rand() * 0.4;
    dummy.updateMatrix();
    buildings.setMatrixAt(placed, dummy.matrix);
    placed++;
  }
  buildings.count = placed;
  scene.add(buildings);

  // --- Window lights: scattered emissive points on facades ---
  const winGeo = new THREE.BufferGeometry();
  const winCount = 900;
  const winPos = new Float32Array(winCount * 3);
  for (let i = 0; i < winCount; i++) {
    const a = rand() * Math.PI * 2;
    const r = 6 + rand() * 20;
    winPos[i * 3] = Math.cos(a) * r;
    winPos[i * 3 + 1] = 0.5 + rand() * 14;
    winPos[i * 3 + 2] = Math.sin(a) * r - 6;
  }
  winGeo.setAttribute('position', new THREE.BufferAttribute(winPos, 3));
  const windows = new THREE.Points(
    winGeo,
    new THREE.PointsMaterial({ color: CYAN, size: 0.09, transparent: true, opacity: 0.75, sizeAttenuation: true })
  );
  scene.add(windows);

  // --- Drifting particles (quiet data traffic) ---
  const dustCount = 300;
  const dustGeo = new THREE.BufferGeometry();
  const dustPos = new Float32Array(dustCount * 3);
  for (let i = 0; i < dustCount; i++) {
    dustPos[i * 3] = (rand() - 0.5) * 50;
    dustPos[i * 3 + 1] = rand() * 18;
    dustPos[i * 3 + 2] = (rand() - 0.5) * 50 - 6;
  }
  dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
  const dust = new THREE.Points(
    dustGeo,
    new THREE.PointsMaterial({ color: SILVER, size: 0.05, transparent: true, opacity: 0.4 })
  );
  scene.add(dust);

  // --- Orbital ring element above the district (concept visual vocabulary) ---
  const ring = new THREE.Mesh(
    new THREE.TorusGeometry(9, 0.06, 8, 96),
    new THREE.MeshBasicMaterial({ color: VIOLET, transparent: true, opacity: 0.35 })
  );
  ring.position.set(0, 16, -10);
  ring.rotation.x = Math.PI / 2.3;
  scene.add(ring);

  // --- Resize ---
  function resize() {
    const w = canvas.clientWidth || canvas.parentElement.clientWidth;
    const h = canvas.clientHeight || canvas.parentElement.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  resize();
  window.addEventListener('resize', resize);

  // --- Pointer parallax (±2° sway), disabled under reduced motion ---
  let targetX = 0, targetY = 0;
  if (!reduce) {
    window.addEventListener('pointermove', (e) => {
      targetX = (e.clientX / window.innerWidth - 0.5) * 0.6;
      targetY = (e.clientY / window.innerHeight - 0.5) * 0.3;
    }, { passive: true });
  }

  // --- Render loop: pause off-screen / when tab hidden ---
  let running = true;
  let frame = 0;
  const clock = new THREE.Clock();

  function render() {
    const t = clock.getElapsedTime();
    if (!reduce) {
      camera.position.x += (targetX - camera.position.x) * 0.03;
      camera.position.y += (2.4 + targetY - camera.position.y) * 0.03;
      camera.lookAt(0, 3, -8);
      ring.rotation.z = t * 0.04;
      dust.rotation.y = t * 0.01;
      windows.material.opacity = 0.55 + Math.sin(t * 0.8) * 0.2; // gentle pulse
    }
    renderer.render(scene, camera);
  }

  function loop() {
    if (!running) return;
    frame = requestAnimationFrame(loop);
    render();
  }

  if (reduce) {
    render(); // single static frame
  } else {
    const io = new IntersectionObserver((entries) => {
      const visible = entries[0].isIntersecting;
      if (visible && !running) { running = true; loop(); }
      running = visible;
      if (visible && !frame) loop();
    }, { threshold: 0.05 });
    io.observe(canvas);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) { running = false; }
      else { running = true; loop(); }
    });
    loop();
  }
}

// Small deterministic PRNG so the skyline is identical every load.
function mulberry32(seed) {
  return function () {
    seed |= 0; seed = (seed + 0x6d2b79f5) | 0;
    let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}
