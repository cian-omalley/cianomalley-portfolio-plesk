<?php
/**
 * Curated, long-form project write-ups.
 *
 * These mirror the real repositories on the owner's GitHub account (public and
 * private) and carry the detailed, humanised case-study bodies used to seed the
 * `project` post type on activation. The live GitHub sync (inc/github-sync.php)
 * refreshes excerpts/status for public repos and can import private ones when a
 * token is configured; the bodies here are the hand-written source of truth and
 * are preserved across syncs.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

/**
 * The curated project set, newest first.
 *
 * Each entry: title, excerpt, status, tech[], year, private (bool),
 * repo (public URL or ''), and body (HTML).
 *
 * @return array<int, array<string, mixed>>
 */
function dd_projects_data() {
	$projects = array();

	/* ------------------------------------------------------------------ *
	 * 1 — SafePulse (private, Go) — the newest and largest project.
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'SafePulse — the Awareness Mesh',
		'excerpt' => 'Privacy-first, offline-capable event safety infrastructure for festivals, raves and clubs — help is always within reach, nobody is ever tracked.',
		'status'  => 'In Progress',
		'tech'    => array( 'Go', 'Privacy', 'Cryptography', 'IoT', 'LoRa', 'PWA' ),
		'year'    => '2026',
		'repo_id' => 1319057074,
		'private' => true,
		'repo'    => '',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>SafePulse is the project I care most about right now, and the one that has taught me the most. It is a privacy-first, offline-capable safety and wayfinding system for the kinds of places where your phone stops working just when you might need it most: festivals, warehouse parties, raves, packed clubs. The idea sounds simple when you say it out loud — a person in trouble should be able to ask for help and be found quickly, without the venue building a surveillance machine to do it. Making that true, in a field with no signal, surrounded by thousands of people, turns out to be a genuinely hard engineering and ethics problem. That tension is the whole project.</p>
<p>I describe the system as an <strong>Awareness Mesh</strong> rather than a tracking network, and the distinction is not marketing. It is the constraint every other decision has to bend around. There is one rule I wrote at the top of the repository and treat as non-negotiable: <em>if a feature tracks people without their explicit, active consent, it is surveillance and it does not ship. If it helps a person who asked for help be found faster while collecting the absolute minimum, it belongs here.</em> Everything below is really just the consequences of taking that sentence seriously.</p>

<h2>The problem, honestly stated</h2>
<p>Crowds break connectivity in ways people underestimate. Cell towers overload the moment tens of thousands of people are in one field. Wi-Fi struggles to push through a wall of bodies — humans are mostly water, and water absorbs the exact radio frequencies we rely on. So the naive answer, "just build an app that pings a server," fails precisely in the situation you built it for. On the worst night, at the worst moment, the little "No Service" indicator appears, and a normal app becomes a dead rectangle of glass.</p>
<p>There is a second, softer problem sitting underneath the technical one. The people who most need to ask for help at an event are frequently not in a calm, rational state. They might be panicking, intoxicated, overstimulated, disoriented, or all four. Any interface I design has to work for someone whose cognitive load is already at its limit. That rules out multi-step flows, logins, menus, and anything that assumes a steady hand and a clear head. The gesture has to be almost impossible to get wrong, and almost impossible to trigger by accident in a pocket full of movement.</p>
<p>And then there is the problem people don't say out loud: safety technology at events has a bad habit of curdling into surveillance. Once you have a system that can locate a distressed guest, you are one product-manager conversation away from a system that locates <em>everyone, all the time,</em> "for their safety." I wanted to build something that is structurally incapable of sliding down that slope — not because a policy says so, but because the data to abuse simply isn't there.</p>

<h2>How it actually works</h2>
<p>The core interaction is deliberately boring, which is the point. A guest presses and holds an SOS control for two to three seconds. That press-and-hold is chosen on purpose: it is deliberate enough that a phone jostling in a pocket won't fire it, but simple enough that a frightened person can do it without thinking. On that hold, the phone seals a tiny encrypted packet — the minimum viable information, essentially "someone in zone X needs help, of this kind, at this time" — and hands it to whatever transport happens to be alive at that instant.</p>
<p>That last part is the trick. The app does not care <em>how</em> the packet travels. It tries the best available path and falls through to the next one automatically: the open internet if it exists, then the venue's local Wi-Fi, then a mesh of small relay nodes passing the packet hop by hop, then long-range LoRa radio for the outdoor edges of a site, and finally — when every electronic path is dead — a printed QR or NFC zone sign and a human being reading it aloud to a staff member. The system is designed so that the <em>last</em> tier always works, because the last tier is paper and a voice.</p>
<p>Wherever the packet lands, it arrives at a <strong>local gateway</strong> — a Raspberry Pi or a small mini-PC sitting on the venue's own network, with no cloud behind it. The gateway is the only thing that holds the keys to open the packet. It decrypts the request and routes it to exactly one team, according to a strict visibility matrix:</p>
<table>
<thead><tr><th>Data</th><th>Awareness</th><th>Medical</th><th>Security</th><th>Organizer</th></tr></thead>
<tbody>
<tr><td>Welfare details</td><td>✔</td><td>—</td><td>—</td><td>—</td></tr>
<tr><td>Triage notes</td><td>—</td><td>✔</td><td>—</td><td>—</td></tr>
<tr><td>Physical threats</td><td>✔</td><td>—</td><td>✔</td><td>—</td></tr>
<tr><td>Aggregated counts</td><td>—</td><td>—</td><td>—</td><td>✔</td></tr>
</tbody>
</table>
<p>Deny by default. A welfare case reaches the welfare team and nobody else. Security only ever sees a physical-threat report, never someone's medical triage. The organizer, who arguably has the most power, sees the <em>least</em> personal data — just anonymous counts so they can staff the night sensibly. The whole point is that no single role can assemble a complete picture of any individual.</p>

<h2>The hardware tiers</h2>
<p>SafePulse version one needs no custom hardware at all: a phone, one computer to run the gateway, and a Wi-Fi router. That was a deliberate design goal, because a system that only works after you buy a crate of electronics is a system that never gets its first real test. Version two adds purpose-built nodes for bigger and rougher sites. There are five node types, and the guiding idea behind all of them is a phrase I keep coming back to: <strong>nodes identify places, not people.</strong></p>
<ul>
<li><strong>QR/NFC zone signs</strong> — completely passive, no power, no battery. Scanning one hands the app a human-readable zone ID like "Main Floor Left." They work when GPS, Bluetooth and Wi-Fi have all failed simultaneously, and a guest can read the zone aloud to staff. They are the humble bedrock of the whole system.</li>
<li><strong>BLE beacon nodes</strong> — small battery units that broadcast an ID many times a second, so a phone can orient itself indoors where GPS is useless. The phone only <em>listens</em>; nothing is ever sent back to the beacon. It is a lighthouse, not a camera.</li>
<li><strong>Relay nodes</strong> — the active workers, built on ESP32-S3 or a Pi Zero. They forward encrypted packets hop by hop using store-and-forward logic. Crucially they are <em>blind</em>: they can read the routing envelope (where a packet must go) but never the encrypted contents. A bucket brigade that cannot see inside the buckets.</li>
<li><strong>Local gateway nodes</strong> — the venue's single source of truth. A Raspberry Pi 5 or mini-PC that decrypts, routes, and holds the temporary event database.</li>
<li><strong>Outdoor LoRa nodes</strong> — long-range radio (Semtech SX1276) for the far corners of an outdoor site, where relays and Wi-Fi don't reach. A long-distance walkie-talkie for data.</li>
</ul>
<p>There is a "privacy gate" built into this arrangement: the mesh has no idea a guest exists until that guest chooses to interact with it. The hardware describes the venue's geography and then waits. Nobody is scanned, counted, or followed for simply being present.</p>

<h2>The cryptography and data lifecycle</h2>
<p>This is where the golden rule becomes actual code rather than good intentions. SOS packets are end-to-end encrypted with a sealed box — X25519 key exchange, XChaCha20-Poly1305 for the payload — using an event-scoped keypair that only the gateway holds. Relay nodes forwarding the packet see nothing but opaque ciphertext and a coarse zone ID. Even if someone physically captured a relay and dumped its memory, they would get noise.</p>
<p>Retention is enforced in code, not policy. Everything identifiable self-deletes on a rolling window, clamped in software to between 24 and 72 hours — you cannot configure it to keep data forever, because the option does not exist. When rows are purged they are securely overwritten on disk, not merely marked deleted. And when the event ends, there is a total-destruction trigger: all identifiable data is wiped and the event keypair is shredded. After that, even ciphertext someone managed to capture during the night becomes permanently unreadable, because the only key that could ever open it no longer exists anywhere in the universe. The one thing that survives a purge is anonymous hourly aggregate counts — numbers with no identifiers attached — so an organizer can learn "the medical tent was busy around 1am" without anyone's night being on record.</p>
<p>Staff don't have accounts in the traditional sense. They're issued tokens — printed or shown as a QR — and only the <em>hashes</em> of those tokens are stored. There are shift-length tokens that rotate, and even a duress decoy token: something a staff member can hand over under coercion that looks like access but isn't. Signing keys let the app pin a venue's manifest fingerprint and reject a spoofed access point pretending to be the gateway. These are the paranoid touches, and in a safety system paranoia is a virtue.</p>

<h2>Graceful degradation as a design philosophy</h2>
<p>If I had to compress SafePulse's engineering into one idea, it would be <strong>graceful degradation</strong>. The system is not built to work perfectly and then fail catastrophically; it is built to keep shedding capability gracefully until it reaches a floor that is essentially unbreakable. Internet becomes local Wi-Fi becomes a relay mesh becomes long-range LoRa becomes a laminated sign and a person's voice. Each tier is worse than the one above it and far better than nothing. A "No Service" message on a phone must never translate into a "No Help" situation for a human being — that sentence is the acceptance test for the entire architecture.</p>
<p>Designing this way changes how you think. You stop asking "will the happy path work?" and start asking "what is the worst realistic night, and does the floor still hold?" Once that becomes the default question, a lot of tempting features quietly disqualify themselves, because they only work in the happy path and add fragility everywhere else.</p>

<h2>What is actually built</h2>
<p>SafePulse is not a slide deck. There is a working reference implementation. The gateway is written in Go, compiles, and passes its test suite — including an interop proof that the Go sealed-box implementation and a libsodium client agree byte for byte, which is exactly the kind of thing that is quietly catastrophic if it's wrong and invisible if you don't test it. The attendee app and the staff dashboard run against the gateway end to end. Firmware sketches cover the relay and LoRa tiers. There are native Android and iOS wrappers (via Capacitor) around the same web UI the gateway serves, so a guest can use an installable app or just a browser — same code underneath.</p>
<p>The repository is deliberately heavy on documentation, because a safety system that only lives in one person's head is itself a risk. There are governance documents on the privacy-by-design and data-minimization protocols, a deployment specification for resilient infrastructure in signal-denied environments, hardware node specifications with real bills of materials and pricing, an operational hierarchy describing who can see what and why, and a strategic development roadmap toward a real pilot. Writing all of that down is not busywork; it is how the golden rule survives contact with other people and future decisions.</p>

<h2>Three apps on a local network</h2>
<p>Concretely, SafePulse is three cooperating pieces that talk over the venue's own network, with no cloud and no accounts between them:</p>
<table>
<thead><tr><th>App</th><th>Who runs it</th><th>What it is</th></tr></thead>
<tbody>
<tr><td><strong>Guest app</strong></td><td>attendees</td><td>Hold-for-help SOS, a silent-help path, zone selection, a wayfinding compass, and an opt-in friend finder — an installable web app served at <code>/app/</code>.</td></tr>
<tr><td><strong>Staff / crew app</strong></td><td>responders &amp; organizers</td><td>A role-scoped incident queue, duress alerts, on-shift location sharing, and event metrics — served at <code>/admin/</code>.</td></tr>
<tr><td><strong>Gateway</strong></td><td>the venue</td><td>The Go server that decrypts SOS packets, routes them to role queues, serves both apps, and enforces data expiry — one Linux or Windows machine.</td></tr>
</tbody>
</table>
<p>Both apps install to the home screen on Android and iOS as progressive web apps — a service worker, a manifest, touch icons — so the zero-install path is just opening a browser on the venue Wi-Fi. There are also dedicated native wrappers built with Capacitor around the exact same web UI, for app-store presence and for the one thing browsers won't give a PWA: background Bluetooth, which the version-two location nodes need. The PWA and the native app are the same code with different reach, which keeps the surface I have to reason about small.</p>

<h2>Version one: no custom hardware</h2>
<p>The whole of version one is designed to run on gear an organiser already owns: one Wi-Fi router, one computer, and everyone's phones. The router doesn't even need an internet connection — it exists only to create a local network everyone joins. The gateway runs on a laptop or mini-PC on that network, serves both apps, and ingests SOS packets. Attendees and staff join the Wi-Fi, open the gateway's address, and that's the system.</p>
<p>This was a deliberate and slightly stubborn choice. It would have been more impressive to design around custom nodes from day one, but a safety system that requires a crate of electronics before its first test is a safety system that never gets tested. Version one exists to prove the privacy model and the interaction design with hardware anyone can assemble in an afternoon. Standing up an event is roughly: build the gateway, write an event manifest describing the venue's zones and the SOS freshness window, mint a handful of staff tokens (printed as QR codes — only their hashes are stored), and run the binary. A TLS proxy goes in front for HTTPS; the gateway itself binds a plain LAN port.</p>

<h2>Version two: location nodes</h2>
<p>Version two adds small anchor nodes at fixed venue landmarks — medical and welfare points, bars, exits, water, stages, the entrance. Each node continuously advertises a BLE beacon carrying its zone ID and kind, plus a <em>rotating, ephemeral</em> beacon ID derived from the event key. The rotation matters: it means a node can't be used to fingerprint the venue over time, and the derivation means it can't be spoofed by an attacker standing up a fake beacon. Phones near a node read its signal strength; the nearest strong beacon tells the guest app which zone the person is in, so an SOS and the "you are here" compass need no manual selection. With several nodes in range the app can trilaterate a rough position — still deliberately coarse, tens of metres — for better bearings to a landmark or a friend.</p>
<p>Crucially, this changes nothing about the privacy stance: nodes announce <em>where a landmark is,</em> never who is near it. The phone listens; nothing is reported back to the node. And it degrades gracefully — the web app keeps working with manual zones and QR/NFC signs at each node, so the location nodes are an enhancement, never a dependency. That "never a dependency" phrase is the recurring test every hardware tier has to pass.</p>

<h2>The threat model, briefly</h2>
<p>Designing a safety system means being honest about who might attack it and how. A captured relay node yields only opaque ciphertext and a coarse zone — no payloads, because relays are blind. A captured gateway is worse, but the rolling expiry and secure-delete mean it holds at most a short window of data, and the event-end shred destroys the keypair so historical ciphertext is unrecoverable. A staff member coerced into handing over access has a duress decoy token that looks like access and isn't. A spoofed access point pretending to be the gateway is rejected because the app pins the manifest's signing fingerprint, printed on the zone signs. None of these defences is exotic on its own; the discipline is applying all of them consistently and refusing the features that would quietly undermine them.</p>

<h2>Key decisions and the trade-offs I accepted</h2>
<p><strong>Local-first, no cloud.</strong> A cloud backend would have been easier to build and operate. I rejected it because a cloud is a honeypot and a single point of failure, and because "the internet is down" is the design case, not an edge case. The cost is that operators have to run a small box on-site; I think that's a fair price for a system that cannot leak what it never centralises.</p>
<p><strong>Minimum data, aggressively enforced.</strong> Collecting more would make some features easier — richer analytics, cross-event history, "personalisation." Every one of those is a reason to hold data, and holding data is the thing I'm trying not to do. So the schema is spartan on purpose and the retention clamp is in the code where a future me can't quietly relax it in a config file.</p>
<p><strong>Press-and-hold over a big instant button.</strong> An instant button has fewer failure modes technically but far more socially: pockets, dancing, hugs. The two-second hold trades a sliver of speed for a huge reduction in false alarms, and false alarms are how a safety system trains its own staff to ignore it.</p>
<p><strong>Blind relays.</strong> Making relays unable to read payloads complicates routing and debugging — you cannot just inspect a packet to see what's wrong. I accepted that friction because a relay that <em>can</em> read is a relay that can be captured and read, and the whole promise collapses.</p>

<h2>What is hard, and what I'd revisit</h2>
<p>The hardest parts are exactly where cryptography meets unreliable radio meets frightened humans. Store-and-forward across a flaky mesh means packets can arrive late, out of order, or twice; the gateway has to be replay-protected and idempotent without keeping so much state that it becomes the very database of behaviour I'm trying to avoid. Getting the retention-and-shred logic provably correct is unglamorous and essential — a bug there isn't a crash, it's a quiet privacy failure nobody notices until it matters.</p>
<p>If I were starting again, I would invest even earlier in the interop and property testing around the crypto and the expiry logic, because those are the areas where "it seems to work" and "it is correct" are dangerously far apart. I would also resist, even harder than I did, the pull toward features that are lovely in a demo and quietly require holding more data. Live location sharing — nearest crew, or a friend's GPS — is genuinely useful and it exists, but strictly as opt-in, because the moment it becomes default it changes what the system fundamentally is.</p>

<h2>Status and where it's going</h2>
<p>SafePulse is a working reference implementation at the pre-pilot stage. The gateway, apps and firmware exist and talk to each other; the roadmap is about hardening, real hardware nodes for version two, and getting it in front of an actual event under controlled conditions. It's private for now while the security model matures, because shipping a half-verified safety system into the world is the one thing worse than not shipping one at all. It sits at the centre of everything else I'm building precisely because it's where my convictions about privacy, resilience and building-for-real-humans stop being abstract and have to survive contact with a field full of people at 2am.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 2 — cianomalley-portfolio-plesk (public, PHP) — this build.
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Portfolio for Plesk — this build',
		'excerpt' => 'A hand-coded, builder-free WordPress theme that runs the cyberpunk portfolio on ordinary Plesk hosting, with a live GitHub project sync.',
		'status'  => 'In Progress',
		'tech'    => array( 'PHP', 'WordPress', 'JavaScript', 'CSS' ),
		'year'    => '2026',
		'repo_id' => 1307738595,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cianomalley-portfolio-plesk',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>This is the site you are reading right now, and it exists because of a constraint I set myself: build the full cyberpunk portfolio experience — the animation, the atmosphere, the character — on completely ordinary hosting, with no proprietary page builder anywhere in the stack. The flagship portfolio is built around Oxygen, a premium builder. This is its sibling: a fork re-imagined as a hand-coded WordPress theme that a person can install on a normal Plesk subscription in about three clicks, with nothing to license and nothing to lock them in.</p>
<p>It is both a working portfolio and a small argument. The argument is that you do not need a heavy drag-and-drop builder to make a fast, distinctive, genuinely enjoyable website. You need a clear design system, some restraint, and a willingness to write the templates yourself. The whole thing is the evidence for that claim.</p>

<h2>Why builder-free, and why WordPress</h2>
<p>Page builders are wonderful for some people and I don't begrudge them. But they come with a cost that is easy to ignore until it bites: they own your layout. If the builder changes its pricing, its data format, or simply stops being maintained, your site is hostage to it. They also tend to ship a lot of markup and JavaScript you didn't ask for, which is the opposite of what I want from a portfolio whose entire pitch is that it feels fast and deliberate.</p>
<p>WordPress, on the other hand, earns its place. It means the site drops straight into Plesk's WP Toolkit, into a huge ecosystem of free SEO and caching plugins, and into a content model — posts, pages, custom post types — that non-technical people already understand. So the decision was: keep WordPress for everything it's genuinely good at, and throw away the builder in favour of plain PHP templates that I control completely.</p>

<h2>Architecture</h2>
<p>The theme is organised around a firm separation between <em>data</em> and <em>presentation</em>. Content lives in custom post types — <code>project</code>, <code>client_work</code>, <code>guide</code>, <code>review</code> — plus native posts for the journal, all sharing a single <code>tech</code> taxonomy so a technology tag means the same thing everywhere. Editors work in the normal WordPress admin with small, nonce-protected meta boxes for the structured fields (status, client, year, live URL, and so on). No ACF, no third-party field plugin; just native WordPress, sanitised on the way in and escaped on the way out.</p>
<pre><code>digital-district/
  functions.php        theme setup, asset loading
  inc/
    post-types.php     project / client_work / guide / review + tech taxonomy
    meta-boxes.php     structured fields, nonce-protected
    template-tags.php  shared rendering (cards, single case study, nav)
    github-sync.php    imports repositories into Projects
    projects-data.php  curated long-form project write-ups
    setup-content.php  one-time build: pages, menu, seeded content
    contact.php        wp_mail contact handling
  assets/
    css/tokens.css     design tokens — the single source of colour/space/type
    css/main.css       the visual system
    js/main.js         progressive-enhancement interactions
    fonts/             self-hosted woff2 (no external calls)</code></pre>
<p>Presentation is driven entirely by CSS custom-property design tokens in <code>tokens.css</code> — every colour, space and type step is defined once and referenced everywhere, so there are no off-token magic values scattered through the styles. The visual identity (acid-lime, violet, magenta and cyan over a near-black canvas) is a handful of variables, which means re-skinning the whole site is editing one file.</p>

<h2>The interaction layer</h2>
<p>All the movement — the custom cursor, scroll-reveal, count-up statistics, magnetic buttons, the 3D card tilt with a spotlight that follows the pointer, the text that decodes as it enters view, the reading-progress bar, and the dependency-free neon-skyline hero rendered on a 2D canvas — is written in vanilla JavaScript as <em>progressive enhancement</em>. That phrase is doing real work. The accessible, server-rendered site is the actual product; it renders and functions with no JavaScript at all. The effects layer on top for people whose device and preferences welcome it, and it respects reduced-motion so it never fights the reader. Making the flashy layer strictly optional is more effort than baking it in, and it is the difference between a site that shows off and one that also lets everyone in.</p>

<h2>The GitHub sync</h2>
<p>Projects aren't maintained by hand. A sync module pulls repositories from GitHub through the REST API, converts a repo's README from Markdown to safe HTML for the body, and — for repos without a README — generates a structured breakdown from the metadata so every project page carries a real explanation instead of a one-liner. It runs on activation and daily via cron, and it preserves any titles or write-ups I've edited by hand. With a personal-access token defined in <code>wp-config.php</code>, it will import private repositories too, marking them clearly and omitting the dead public link. It's a small feature that removes an entire category of tedious bookkeeping.</p>

<h2>Privacy and self-hosting posture</h2>
<p>There are no third-party calls and no trackers. The three typefaces are self-hosted as woff2 with unicode-range slicing, so no font CDN ever sees a visitor. That isn't only a principle; it's practical. A site with no external dependencies is comfortable to run on modest self-hosted infrastructure or a small Plesk plan, it can't be slowed down by someone else's server, and it can't quietly leak your readers to an analytics vendor you forgot you added.</p>

<h2>Decisions and trade-offs</h2>
<p><strong>Native meta boxes over ACF.</strong> ACF would have been faster to wire up, but it's another dependency and another thing an installer has to have. Hand-rolled meta boxes keep the theme self-contained. <strong>A 2D canvas hero over Three.js.</strong> The flagship has a real 3D scene; here I wanted zero heavy dependencies and instant load, so the hero is a lightweight canvas animation that evokes the same neon city without shipping a WebGL engine. <strong>Seed on activation.</strong> Rather than hand a new user an empty site, the theme builds itself — pages, menu, sample content — the moment it's activated, so it looks like the demo immediately and they edit from there.</p>

<h2>What I'd do differently</h2>
<p>An early version leaned too hard on the text-scramble effect, and on long labels it briefly turned into unreadable noise. Capping it to short strings fixed the symptom, but the lesson was broader: an effect that degrades legibility, even for a moment, has to be constrained hard or dropped. I also learned to keep the seeding logic idempotent and self-healing — the first menu build shipped an entry I later removed, and because menus only rebuild on activation, existing installs kept the stale item until I added a one-time cleanup. Building for "sites that already exist," not just fresh installs, is a discipline I under-rated at first.</p>

<h2>Status</h2>
<p>In progress and shipping — this build is live, it's the reference this very page runs on, and it keeps gaining polish. It's the pragmatic, portable counterpart to the flagship: same identity, none of the lock-in.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 3 — Hermes Workspace OS (public, Python).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Hermes Workspace OS',
		'excerpt' => 'An agent-driven workspace operating layer — coordinating tools, memory and tasks so AI agents can actually get work done across sessions.',
		'status'  => 'In Progress',
		'tech'    => array( 'Python', 'AI', 'Agents', 'Systems' ),
		'year'    => '2026',
		'repo_id' => 1306656088,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/Hermes-Workspace-OS',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>Hermes Workspace OS is where I work out, in practice, what it takes to turn a chatbot into something that behaves like a colleague. The difference between the two is not intelligence — the underlying models are the same — it's <em>context and continuity</em>. A chatbot answers a question and forgets you. A workspace remembers what you were doing, knows which tools it can reach for, and can pick a task back up tomorrow. Hermes is the coordination layer that makes the second thing possible.</p>
<p>It is named for the messenger — the thing that carries information between parties and makes sure it arrives. That's the honest description of what the software does: it sits between an agent, its tools, and its memory, and it keeps the messages flowing in a way that's predictable enough to trust.</p>

<h2>The problem it solves</h2>
<p>If you've built anything with language models, you've hit the same wall I did. A single prompt is easy. The moment you want the model to <em>do</em> a sequence of things — look something up, act on it, remember the result, come back to it later — you're no longer writing prompts, you're writing an operating system for a very capable but very forgetful worker. You need a registry of tools it can call, a memory it can rely on between runs, and a task model so work can be planned and resumed. Without those, every conversation starts from zero and nothing compounds.</p>
<p>Hermes exists so that intelligence can compound. A task begun today should be continuable tomorrow without re-explaining everything. A result discovered in one session should be available in the next. That's the entire pitch, and it's harder than it sounds.</p>

<h2>Architecture</h2>
<p>The system is organised around three cooperating concerns, and I built them roughly in this order because each one depends on the last being solid:</p>
<ul>
<li><strong>The tool layer</strong> — a catalogue of capabilities an agent can call, behind a predictable, well-typed interface. The design goal is that adding a new tool is boring: describe what it does and what it takes, and the orchestration loop can use it without special-casing. Boring is the highest compliment I can pay an interface.</li>
<li><strong>Memory</strong> — a durable store that survives between sessions, so context isn't thrown away when a conversation ends. This is the hard part. The naive version — remember everything, search it later — makes every decision slow and noisy. The working design is layered: a small, always-present working set for the immediate task, plus a larger store queried deliberately only when older context is actually needed.</li>
<li><strong>Tasks and orchestration</strong> — a model for planning, queuing and resuming work, and a loop that is transparent about what it's doing and why. Transparency is a feature here, not a nicety: an agent that acts on its own is only useful if you can see and trust what it did.</li>
</ul>

<h2>Build philosophy</h2>
<p>Written in Python, and built with the same discipline I apply everywhere: get the core loops correct before layering capabilities on top. It's tempting, with agents, to chase the impressive demo — the flashy multi-step task that wows in a screen recording. I've learned the hard way that surface features built on an unstable foundation keep destabilising the foundation. So Hermes leads with the unglamorous plumbing — a clean tool interface, a queryable memory, a loop you can reason about — and adds visible capability only once the layer beneath it is dependable.</p>

<h2>Decisions and trade-offs</h2>
<p><strong>Layered memory over "remember everything."</strong> Storing all history is simplest and worst; it turns retrieval into a slow, noisy guessing game. The layered approach keeps day-to-day interactions fast while still letting old context resurface on purpose. The cost is more machinery around what to keep hot and what to archive — machinery I think is worth it. <strong>Transparent orchestration over maximum autonomy.</strong> A more autonomous agent is more impressive and less trustworthy; I bias toward loops I can audit. <strong>Typed, boring tool interfaces over clever ones.</strong> Cleverness in the tool layer is a tax you pay every time you add a tool; predictability compounds.</p>

<h2>What's hard, and what I'd revisit</h2>
<p>Memory is the perennial hard problem — deciding what to remember, how long to keep it, and how to fetch the right fragment at the right moment without drowning the model in irrelevance. It's less a solved feature than a knob I keep tuning. If I were starting over, I'd freeze the core loops even earlier and resist the pull of capability-first development harder than I did; the moment the interesting features and the foundation grow together, the features win the calendar and the foundation suffers. Hermes feeds directly into my larger AI Operating System work — it's the place where the orchestration ideas get proven at a smaller, sharper scale before they graduate.</p>

<h2>Status</h2>
<p>Under active development, foundation-first. The core loops are the current focus; the more visible agent capabilities come next, once the plumbing underneath them is genuinely dependable.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 4 — AI Operating System (private, Python).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'AI Operating System',
		'excerpt' => 'A personal operating system where AI agents coordinate tools, long-term memory and scheduled tasks — the way an OS coordinates programs and processes.',
		'status'  => 'In Progress',
		'tech'    => array( 'Python', 'AI', 'Agents', 'Systems' ),
		'year'    => '2026',
		'repo_id' => 1306656088,
		'private' => true,
		'repo'    => '',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>The AI Operating System is the largest and most active of my AI projects, and it's the umbrella the others feed into. The idea is in the name: an environment where AI agents coordinate tooling, long-term memory and scheduled tasks the way a real operating system coordinates programs, files and processes. Not a single clever assistant, but a system that quietly keeps working on your behalf — planning, remembering, acting on a schedule — the way a computer keeps doing its job whether or not you're staring at it.</p>

<h2>The problem it solves</h2>
<p>Most AI tooling today is reactive. You ask, it answers, and nothing persists. That's fine for a lookup and useless for anything that unfolds over time. I wanted the opposite default: a system that is <em>proactive and durable</em>, that can hold a goal across days, remember what it learned, and do work when the moment is right rather than only when I'm typing. The mental model I keep returning to is the operating system — the thing that makes it possible to run many programs, share resources between them, and keep state safely — but with agents as the processes and tasks as the workload.</p>

<h2>Architecture</h2>
<p>At its core the system manages three things, and getting each one right is a project in itself:</p>
<ul>
<li><strong>A tool catalogue</strong> — the set of capabilities agents can call, behind a stable interface. This is where the boundary between "the model" and "the world" lives, and it needs to be predictable and safe, because a tool call is the point where an agent stops talking and starts acting.</li>
<li><strong>Durable memory</strong> — a store agents read from and write to, so context survives between sessions. Memory is what separates a scheduler-with-a-chatbot from something that genuinely accumulates understanding.</li>
<li><strong>A scheduler and task model</strong> — so work happens on its own. This is the piece that turns "answer when asked" into "act when appropriate," and it's the reason the whole thing deserves the word <em>operating</em>.</li>
</ul>
<p>Hermes Workspace OS is the sharper, smaller sibling where the orchestration ideas get proven; the AI Operating System is where they scale up into a full environment with scheduling and a larger tool surface.</p>

<h2>Build philosophy and trade-offs</h2>
<p>Architecture and core loops first; visible capabilities second. I've made the mistake of doing it the other way — growing the interesting agent features and the underlying system in parallel — and the features kept knocking the foundation over. Now the rule is firm: prove the plumbing (a dependable tool interface, a queryable memory, a transparent orchestration loop, a scheduler you can trust) before adding anything that shows up in a demo. It feels slower in the moment and it repeatedly pays for itself.</p>
<p>The recurring hard problems are the ones you'd expect from anything that acts on its own: memory (what to keep, for how long, how to retrieve the right thing without noise) and autonomy-versus-control (an agent that acts on a schedule is only valuable if you can audit what it did, so I treat transparency in the loop as a first-class feature, not a debugging afterthought). It's a substantial, active Python codebase, private while the architecture settles.</p>

<h2>Status</h2>
<p>Under active development — the most active of the AI projects. The foundation is the current focus; the visible agent capabilities come next, once everything underneath them is dependable.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 5 — Cianomalley Documentation (public).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Ecosystem Documentation Hub',
		'excerpt' => 'The central memory of the whole ecosystem — architecture decisions, deployment guides, workflows and the reasoning behind them.',
		'status'  => 'In Progress',
		'tech'    => array( 'Docs', 'Architecture' ),
		'year'    => '2026',
		'repo_id' => 1294612451,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cianomalley-documentation',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>If the other repositories are the work, this one is the memory of how and why the work was done. The documentation hub is the single place where architecture decisions, planning, deployment and infrastructure guides, and development workflows live for the entire cianomalley ecosystem. It's the thing that lets a collection of separate repositories behave like one coherent system instead of a pile of unrelated experiments.</p>

<h2>Why a dedicated documentation repository</h2>
<p>I learned quickly that undocumented decisions don't survive. You make a sharp architectural choice on a Tuesday, you're certain you'll remember the reasoning, and three weeks later you're staring at your own code wondering what past-you was thinking. Multiply that across a dozen repositories and the ecosystem starts to drift — each project quietly reinventing conventions the others already settled. A central documentation hub is the antidote: write the decision and its <em>reasoning</em> down once, in a place everything else can point to.</p>

<h2>What it holds</h2>
<ul>
<li><strong>Architecture notes</strong> — not just what was decided, but why, and what was rejected. The rejected options are often more useful than the chosen one, because they stop you re-litigating settled questions.</li>
<li><strong>Deployment and infrastructure guides</strong> — step-by-step, so a deploy is a checklist rather than an act of memory. This is where the self-hosting and Plesk knowledge gets written down properly.</li>
<li><strong>Development workflows</strong> — the conventions that keep projects consistent with one another: how things are named, structured, and shipped.</li>
</ul>

<h2>The principle behind it</h2>
<p>Writing things down here is what lets a set of separate repositories act like one product. It's the least glamorous repository I own and arguably the most important, because it's the connective tissue. Documentation is a force multiplier: every hour spent making a decision legible saves several hours of confusion later, across every project that touches it.</p>

<h2>Status</h2>
<p>In progress and growing alongside the projects it documents — it's never "done" by design, because the moment it stops keeping pace with the code, it stops being trustworthy.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 6 — Cianomalley Portfolio (public, PHP) — the flagship.
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Interactive 3D Portfolio (flagship)',
		'excerpt' => 'The cinematic front door to cianomalley.works — a 3D cyberpunk portfolio bringing software, hardware, repositories and writing into one experience.',
		'status'  => 'In Progress',
		'tech'    => array( 'PHP', 'WordPress', 'Three.js', 'Frontend' ),
		'year'    => '2026',
		'repo_id' => 1294601843,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cianomalley-portfolio',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>This is the flagship — the cinematic, cyberpunk front door to cianomalley.works, and the reference that the Plesk build was forked from. It brings every strand of what I do into one place: software projects, hardware, GitHub repositories, blog posts, tutorials, reviews and technical write-ups, behind an interactive experience designed to be memorable rather than merely functional.</p>

<h2>What it's for</h2>
<p>A portfolio has one job: make a stranger understand who you are and what you can do, quickly, and want to keep reading. Most developer portfolios are a list of links. I wanted mine to feel like a place — to have atmosphere — while never letting the atmosphere get in the way of the substance underneath. The spectacle is there to earn attention; the readable, accessible content is there to reward it.</p>

<h2>Architecture</h2>
<p>Built on WordPress with a custom PHP theme, so content lives in a model I control and the front end is exactly what I write, not what a builder emits. The signature is a single, lazily-loaded Three.js scene — a dense mini-city viewed from street level, camera kept low so the edges of the model are never visible, so a modest amount of geometry reads as a sprawling city. Around it sit the interaction touches: cursor-reactive cards, scroll-driven reveals, decoding text. All of it is progressive enhancement over an accessible baseline that ships first and works without any of it.</p>

<h2>The 3D decision</h2>
<p>The 3D scene is the highest-risk, highest-reward part, and the constraints on it are deliberate. One scene, not many, because a portfolio that stutters is worse than one that's plain. Street-level camera with hidden edges, because that's what turns a small model into a convincing city and keeps the polygon budget honest. Lazy-loaded, so the WebGL engine never blocks first paint. And strictly optional — the non-3D site is the real product and the 3D is the flourish on top, gated behind capability and reduced-motion checks. The order matters: accessible first, spectacle second, always.</p>

<h2>Its place in the ecosystem</h2>
<p>The flagship is the front door; the Plesk build is its portable, builder-free sibling for ordinary hosting; the design system and brand repositories feed both with a shared vocabulary; the documentation hub records the decisions. Seeing them together is the point — a set of projects that deliberately share a design language so they read as one product.</p>

<h2>Status</h2>
<p>Under active development, and the origin point that the rest of the portfolio work references.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 7 — Cianomalley Design System (public).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Design System',
		'excerpt' => 'Design tokens, components, motion and 3D interaction concepts — the shared vocabulary every site in the ecosystem is built from.',
		'status'  => 'Planning',
		'tech'    => array( 'Design', 'CSS', 'Tokens' ),
		'year'    => '2026',
		'repo_id' => 1294598610,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cianomalley-design-system',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>The design system is the foundation everything visual is built from: brand guidelines, design tokens, components, motion and 3D interaction concepts, gathered in one place so every site in the ecosystem speaks the same language instead of being reinvented each time. It's the reason the flagship portfolio and the Plesk build feel like relatives rather than strangers.</p>

<h2>Why build a system first</h2>
<p>Building a design system before you strictly need one feels slow on day one and pays off relentlessly after. The alternative — designing each page from scratch — produces sites that drift: three slightly different blues, four spacing scales, buttons that don't quite match. A shared set of tokens (colour, type, spacing, motion) and components means every new page is assembled from parts that already exist and already work. The first page is slower; every page after is faster and more consistent.</p>

<h2>What it holds</h2>
<ul>
<li><strong>Design tokens</strong> — the colour, type, spacing and motion values the themes consume directly, so a change to the vocabulary propagates everywhere at once.</li>
<li><strong>Components</strong> — the reusable patterns that make new pages quick to build.</li>
<li><strong>Motion and 3D concepts</strong> — the interaction ideas that give the work its identity, documented so they're applied consistently rather than improvised.</li>
</ul>

<h2>Status</h2>
<p>In planning — the repository is the home for the system while its shape is worked out before the bulk of it is built. Deliberately deliberate: a design system built in a hurry is just inconsistency with extra steps.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 8 — Cianomalley Assets (public).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Asset Library',
		'excerpt' => 'One versioned home for logos, icons, mockups, 3D models, CV files and branding — a single source of truth for every project.',
		'status'  => 'In Progress',
		'tech'    => array( 'Assets', 'Branding' ),
		'year'    => '2026',
		'repo_id' => 1294604194,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cianomalley-assets',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>The asset library is the shared home for everything visual the ecosystem needs: logos, icons, screenshots, mockups, wallpapers, 3D models, textures, CV files and branding resources, for both cianomalley.works and cianomalley.dev. It exists so that every project pulls from the same source of truth instead of quietly drifting apart.</p>

<h2>The problem it prevents</h2>
<p>Without a central library, assets duplicate. A logo ends up in three repositories, each a slightly different export, and eventually nobody knows which is canonical. A mockup gets tweaked in one place and not the others. It's a small, unglamorous kind of entropy, and it compounds. Versioning everything visual in one place means a logo or a mockup is never three subtly-different copies scattered across three projects — there's one, and everything references it.</p>

<h2>What it holds</h2>
<p>Everything visual the ecosystem depends on, versioned together: brand marks and their variants, UI icons, product screenshots and mockups, 3D models and textures for the interactive scenes, wallpapers, and document assets like CV exports. Keeping it under version control means changes are tracked and reversible, the same way code is.</p>

<h2>Status</h2>
<p>In progress, filled out as the projects that depend on it come online. It grows in step with demand rather than ahead of it.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 9 — Cianomalley Brand (public).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Brand Identity',
		'excerpt' => 'Identity, messaging, writing style and visual guidelines — the reference that keeps the voice and the look consistent everywhere.',
		'status'  => 'Planning',
		'tech'    => array( 'Brand', 'Design' ),
		'year'    => '2026',
		'repo_id' => 1294612993,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cianomalley-brand',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>The brand repository is where the identity lives: messaging, design direction, writing style, logo concepts, social assets and visual guidelines for the whole portfolio ecosystem. It's the reference that keeps both the voice and the look consistent wherever they appear.</p>

<h2>Why brand deserves its own repository</h2>
<p>It's easy to think of brand as just a logo and a colour. In practice the most valuable part is the <em>voice</em> — how things are said, not only how they look. A consistent voice across a portfolio, a set of guides, and a handful of project pages is what makes them feel authored by one person with a point of view, rather than assembled from templates. Writing the guidelines down — tone, vocabulary, the difference between how I write a technical guide and how I write a project story — is what makes that consistency survive being tired, rushed, or three months removed from the last time I thought about it.</p>

<h2>What it holds</h2>
<p>The written and visual guidelines that the portfolio, the design system, and the asset library all defer to: identity and logo direction, messaging and positioning, the writing-style rules that keep the copy sounding like one person, and the social and visual assets that carry the brand into other places.</p>

<h2>Status</h2>
<p>In planning, being scoped alongside the design system it sits next to — the two are close cousins and it makes sense to settle them together.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 10 — Project Template (public, PowerShell).
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'Project Template',
		'excerpt' => 'A reusable scaffold for starting development, creative and research projects consistently — so the first hour goes to the work, not the setup.',
		'status'  => 'Complete',
		'tech'    => array( 'PowerShell', 'Tooling' ),
		'year'    => '2026',
		'repo_id' => 1287575210,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/project-template',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>The project template is a reusable scaffold — a ready-made folder structure and a little automation for starting development, creative, documentation, research and portfolio projects the same way every time. It's a small tool with an outsized effect on how a new project begins.</p>

<h2>Why it exists</h2>
<p>The first hour of a new project is usually wasted on setup: making the same folders, copying the same config, remembering the conventions you settled on last time. Multiply that across a dozen projects and it's a real tax, and worse, each ad-hoc setup drifts a little from the last, so nothing quite matches. A template turns that first hour into a single step. You start consistent instead of improvising, and the structure you land on is the one you already decided was good — not whatever you half-remembered at the time.</p>

<h2>What it provides</h2>
<p>A sensible default layout for the common project types, and the small pieces of PowerShell automation that go with it, so a new project arrives pre-organised. It encodes the conventions once so they don't have to be re-derived every time.</p>

<h2>Status</h2>
<p>Complete — a finished, reusable artefact rather than a moving target. It's the kind of tool that's "done" precisely because it does one small job well, and I reach for it whenever a new project begins.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 11 — cian-omalley (public) — profile repository.
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'GitHub Profile',
		'excerpt' => 'The special profile repository whose README becomes the GitHub profile page — the introduction that points to everything else.',
		'status'  => 'Planning',
		'tech'    => array( 'Profile', 'Docs' ),
		'year'    => '2026',
		'repo_id' => 1286882906,
		'private' => false,
		'repo'    => 'https://github.com/cian-omalley/cian-omalley',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>This is the special repository whose README becomes my GitHub profile page — the short introduction a visitor sees first, and the signpost that points them to the rest of the ecosystem.</p>

<h2>Why it matters more than its size suggests</h2>
<p>It's a tiny repository by content, but it's disproportionately important because it's often the first thing a stranger reads. A profile page has seconds to say who someone is and give them a reason to look further. So the job here isn't volume, it's clarity: a concise introduction, an honest sense of what I'm building, and clean routes into the projects that matter. It's the human-readable index to everything else.</p>

<h2>Status</h2>
<p>In planning — small by nature, and shaped to line up with the brand and design system as those settle, so the first impression matches the rest of the work.</p>
HTML
	);

	/* ------------------------------------------------------------------ *
	 * 12 — Skills: Introduction to GitHub (private) — learning exercise.
	 * ------------------------------------------------------------------ */
	$projects[] = array(
		'title'   => 'GitHub Skills Course',
		'excerpt' => "GitHub's official Introduction to GitHub Skills course, worked through end to end — kept for the record.",
		'status'  => 'Complete',
		'tech'    => array( 'GitHub', 'Git', 'Learning' ),
		'year'    => '2026',
		'repo_id' => 1286874059,
		'private' => true,
		'repo'    => '',
		'body'    => <<<'HTML'
<h2>Overview</h2>
<p>This is the repository for GitHub's official "Introduction to GitHub" Skills course — a guided, hands-on walk through the fundamentals of the platform, worked through end to end.</p>

<h2>Why keep a learning exercise</h2>
<p>It would be easy to delete a completed tutorial, but I keep it deliberately. Part of an honest portfolio is showing the groundwork, not only the finished projects. Everyone starts somewhere, and a record of deliberately learning the fundamentals — branches, commits, pull requests, the actual mechanics of collaborating in Git — is part of the story, not something to hide. It's a finished exercise, kept for the record rather than an ongoing project.</p>

<h2>Status</h2>
<p>Complete.</p>
HTML
	);

	return $projects;
}
