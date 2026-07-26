<?php
/**
 * One-time setup on theme activation: register rewrite rules, create the
 * About/Contact pages, build a primary menu with friendly labels, and seed the
 * owner's honest personal projects. Client Work is intentionally left empty —
 * no clients are invented; the owner adds real client projects in wp-admin.
 *
 * @package DigitalDistrict
 */

defined( 'ABSPATH' ) || exit;

function dd_after_switch_theme() {
	dd_register_content();
	flush_rewrite_rules();

	dd_create_pages();

	// Import every repository from the GitHub account. If the server can't
	// reach GitHub during activation, fall back to a small honest seed so the
	// site isn't empty; the owner can re-run the sync from Projects → Sync GitHub.
	$synced = function_exists( 'dd_sync_github' ) ? dd_sync_github() : 0;
	if ( is_wp_error( $synced ) || 0 === $synced ) {
		dd_seed_projects();
	}

	dd_seed_guides();
	dd_seed_reviews();
	dd_seed_client_templates();
	dd_seed_posts();
	dd_build_primary_menu();

	if ( ! wp_next_scheduled( 'dd_github_cron' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'dd_github_cron' );
	}
}
add_action( 'after_switch_theme', 'dd_after_switch_theme' );

/**
 * Tidy up the cron when the theme is switched away.
 */
function dd_on_switch_theme() {
	$ts = wp_next_scheduled( 'dd_github_cron' );
	if ( $ts ) {
		wp_unschedule_event( $ts, 'dd_github_cron' );
	}
}
add_action( 'switch_theme', 'dd_on_switch_theme' );

/**
 * Self-heal older installs: the first menu build shipped a "Project Breakdown"
 * item (and a /breakdown/ page) that has since been removed. Existing sites keep
 * the stale nav entry because the menu is only built on theme activation, so run
 * a one-time cleanup that removes the leftover menu item and page. Guarded by an
 * option flag, so it runs at most once.
 */
function dd_cleanup_breakdown() {
	if ( get_option( 'dd_breakdown_removed' ) ) {
		return;
	}
	update_option( 'dd_breakdown_removed', 1 );

	// Remove any nav menu item pointing at the old /breakdown/ page.
	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( wp_get_nav_menu_items( $menu->term_id ) as $item ) {
			if ( false !== strpos( (string) $item->url, '/breakdown' )
				|| 'Project Breakdown' === $item->title ) {
				wp_delete_post( $item->ID, true );
			}
		}
	}

	// Remove the leftover page itself, if it still exists.
	$page = get_page_by_path( 'breakdown' );
	if ( $page ) {
		wp_delete_post( $page->ID, true );
	}
}
add_action( 'init', 'dd_cleanup_breakdown' );

/**
 * Create the About and Contact pages and set a static front page.
 */
function dd_create_pages() {
	// Front page (uses front-page.php automatically).
	$front = get_page_by_path( 'home' );
	if ( ! $front ) {
		$front_id = wp_insert_post( array(
			'post_title'   => __( 'Home', 'digital-district' ),
			'post_name'    => 'home',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
		if ( $front_id && ! is_wp_error( $front_id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front_id );
		}
	}

	if ( ! get_page_by_path( 'about' ) ) {
		wp_insert_post( array(
			'post_title'   => __( 'About', 'digital-district' ),
			'post_name'    => 'about',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => "I build systems — self-hosted infrastructure, developer tooling, and interactive software — and document how they are made. Based in Stuttgart, Germany.\n\nEdit this page in wp-admin to tell your story.",
		) );
	}

	if ( ! get_page_by_path( 'contact' ) ) {
		wp_insert_post( array(
			'post_title'    => __( 'Contact', 'digital-district' ),
			'post_name'     => 'contact',
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'page_template' => 'page-contact.php',
			'post_content'  => '',
		) );
	}

	// Blog: a posts page so the native post type has a home in the nav.
	$blog = get_page_by_path( 'blog' );
	if ( ! $blog ) {
		$blog_id = wp_insert_post( array(
			'post_title'  => __( 'Blog', 'digital-district' ),
			'post_name'   => 'blog',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
	} else {
		$blog_id = $blog->ID;
	}
	if ( $blog_id && ! is_wp_error( $blog_id ) ) {
		update_option( 'page_for_posts', $blog_id );
	}
}

/**
 * Seed guide topics (honest, from the discovery record). Statuses are honest —
 * these are planned/in-progress writing, not claimed as published.
 */
function dd_seed_guides() {
	if ( get_posts( array( 'post_type' => 'guide', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
		return;
	}
	$guides = array(
		array(
			'Self-Hosting from a Home Server',
			'Standing up a portable web stack at home behind a Cloudflare Tunnel.',
			'In Progress', '9 min', array( 'Self-Hosting', 'Nginx', 'Docker' ),
			"<h2>Why self-host first</h2>\n<p>Owning the stack means no lock-in and a clear migration path. The same stack — Nginx, PHP 8.3, MariaDB, Redis — runs at home now and on a VPS later, so moving is an rsync and a DNS change, not a rebuild.</p>\n<h2>The stack</h2>\n<ul><li><strong>Nginx</strong> as the web server and reverse proxy.</li><li><strong>PHP 8.3</strong> and <strong>MariaDB</strong> for WordPress.</li><li><strong>Redis</strong> for object caching.</li><li><strong>Cloudflare Tunnel</strong> so the residential IP is never exposed.</li></ul>\n<h2>Keeping it portable</h2>\n<p>No panel-only assumptions live in the site itself, backups go off-site, and both domains sit behind Cloudflare — so the eventual VPS move is painless.</p>",
		),
		array(
			'Building with Hermes Agent',
			'Patterns and notes from working with the Hermes Workspace OS tooling.',
			'In Progress', '7 min', array( 'AI', 'Python' ),
			"<h2>What Hermes is</h2>\n<p>An agent-driven workspace operating layer — coordinating tools, memory, and tasks. This guide collects the patterns that have held up while building it.</p>\n<h2>Architecture first</h2>\n<p>Get the core loops right before adding capabilities: a clear task model, durable memory, and a predictable tool interface.</p>",
		),
		array(
			'WordPress Without a Page Builder',
			'A fast, maintainable WordPress site using core tooling — like this one.',
			'Published', '11 min', array( 'WordPress', 'PHP' ),
			"<h2>Hand-coded, not builder-locked</h2>\n<p>This very site is a hand-coded theme: PHP templates, CSS custom properties, and a little vanilla JavaScript. No page builders — nothing you cannot move off in an afternoon.</p>\n<h2>Custom post types do the heavy lifting</h2>\n<p>Projects, client work, guides, and reviews are custom post types with their own templates. Content lives in the database and is edited in wp-admin; the theme just renders it.</p>\n<h2>Keep SEO plugins happy</h2>\n<p>Declare <code>title-tag</code> support and never hard-code titles, so Yoast, The SEO Framework, or Rank Math can manage metadata cleanly.</p>",
		),
		array(
			'A JetBrains-Centred Workflow',
			'Getting the most out of JetBrains IDEs day to day.',
			'Planned', '6 min', array( 'JetBrains' ),
			"<h2>One IDE, many languages</h2>\n<p>Notes on running a polyglot workflow — PHP, Python, and TypeScript — from JetBrains tooling, and the shortcuts and inspections that actually save time.</p>",
		),
		array(
			'Getting Started with Docker',
			'Containers explained from scratch — images, volumes, ports, and your first running app.',
			'Published', '10 min', array( 'Docker', 'Self-Hosting' ),
			"<h2>What Docker actually is</h2>\n<p>Docker packages an application together with everything it needs to run — the runtime, the libraries, the config — into a single unit called a <strong>container</strong>. Unlike a virtual machine, a container does not carry a whole operating system with it; it shares the host's kernel and only bundles what sits above it. That is why containers start in milliseconds, weigh megabytes rather than gigabytes, and run the same on your laptop, a home server, and a VPS. For self-hosting this is the whole game: “works on my machine” stops being a problem, because the machine travels with the app.</p>\n<h2>Images vs containers</h2>\n<p>The two words trip everyone up at first. An <strong>image</strong> is the read-only blueprint — a snapshot of a filesystem plus the command to start. A <strong>container</strong> is a running instance of an image, with its own writable layer on top. You pull or build an image once, then run as many containers from it as you like. When a container stops, its writable layer is thrown away unless you deliberately keep data outside it (see volumes).</p>\n<h2>Your first container</h2>\n<p>The canonical first run is a web server:</p>\n<pre><code>docker run --name web -p 8080:80 nginx</code></pre>\n<p>That pulls the <code>nginx</code> image, starts a container named <code>web</code>, and maps port 80 inside the container to 8080 on your machine, so <code>http://localhost:8080</code> serves it. Add <code>-d</code> to run it in the background, <code>docker ps</code> to see what is running, and <code>docker logs web</code> to read its output.</p>\n<h2>Ports and volumes — the two things that matter</h2>\n<p>A container is isolated by default, so two flags do most of the work. <code>-p host:container</code> exposes a port to the outside. <code>-v /path/on/host:/path/in/container</code> mounts a folder (or a named volume) so data survives restarts and upgrades — databases, uploads, and config all live in volumes, never inside the container's throwaway layer. Get those two ideas and you can run almost anything.</p>\n<h2>Why it matters for self-hosting</h2>\n<p>Every self-hosted app worth running ships a Docker image. Instead of chasing dependencies and PHP or Python versions across a server, you pull an image and run it, isolated from everything else. Upgrading is pulling a newer image and recreating the container; rolling back is pulling the old tag. The next guide — Docker Compose — turns a pile of single <code>docker run</code> commands into one tidy file.</p>\n<h2>A short cheat sheet</h2>\n<ul><li><code>docker ps -a</code> — list all containers, running or not.</li><li><code>docker images</code> — list downloaded images.</li><li><code>docker exec -it web sh</code> — open a shell inside a running container.</li><li><code>docker stop web &amp;&amp; docker rm web</code> — stop and remove.</li><li><code>docker system prune</code> — reclaim space from stopped containers and dangling images.</li></ul>",
		),
		array(
			'Docker Compose: From One Container to a Stack',
			'Describe a whole multi-service app — web, database, cache — in one file and bring it up with a single command.',
			'Published', '12 min', array( 'Docker', 'Self-Hosting' ),
			"<h2>Why Compose exists</h2>\n<p>Real apps are rarely one container. A web app needs a database; the database might need a cache; there may be a background worker and a reverse proxy in front. Wiring those together with individual <code>docker run</code> commands — remembering the right flags, ports, networks, and start order every time — is miserable. Docker Compose lets you describe the whole thing in one declarative file and manage it as a single unit: <code>docker compose up</code> to start everything, <code>docker compose down</code> to stop it.</p>\n<h2>The anatomy of a compose file</h2>\n<p>A <code>compose.yaml</code> has three ideas: <strong>services</strong> (your containers), <strong>volumes</strong> (persistent data), and <strong>networks</strong> (how services talk). Compose creates a private network automatically, and — crucially — services reach each other by name. Your web app connects to the database at the host <code>db</code>, not an IP address.</p>\n<pre><code>services:\n  web:\n    image: nginx\n    ports:\n      - \"8080:80\"\n    depends_on:\n      - db\n  db:\n    image: mariadb:11\n    environment:\n      MARIADB_ROOT_PASSWORD: change-me\n    volumes:\n      - dbdata:/var/lib/mysql\nvolumes:\n  dbdata:</code></pre>\n<h2>The everyday commands</h2>\n<p><code>docker compose up -d</code> builds/pulls and starts everything in the background. <code>docker compose logs -f</code> tails the combined logs. <code>docker compose ps</code> shows status. <code>docker compose down</code> stops and removes the containers and network — add <code>-v</code> only when you truly want to delete the volumes too. Change the file and run <code>up -d</code> again and Compose recreates just the services that changed.</p>\n<h2>Environment and secrets</h2>\n<p>Keep configuration out of the file itself. A <code>.env</code> file next to the compose file is read automatically, so <code>MARIADB_ROOT_PASSWORD: \${DB_PASS}</code> pulls from the environment. For anything sensitive, use Docker secrets or an <code>env_file:</code> that is git-ignored — never commit passwords into the compose file.</p>\n<h2>Updating a stack safely</h2>\n<p>The upgrade loop is calm: pin image tags (<code>mariadb:11</code>, not <code>latest</code>), <code>docker compose pull</code> to fetch new versions, then <code>up -d</code> to recreate. Because data lives in named volumes, the containers are disposable and the database is not. Back the volumes up on a schedule and a bad upgrade is a one-line rollback to the previous tag.</p>\n<h2>Where it leads</h2>\n<p>Almost every self-hosted project publishes a ready-made compose file. Once you can read one, you can run and maintain a knowledge base, a media server, an analytics tool, or a whole WordPress site as a single, version-controlled stack.</p>",
		),
		array(
			'Self-Hosting 101: Own Your Stack',
			'From a mini PC to a real service — the reverse proxy, containers, backups, and exposing it safely without opening ports.',
			'In Progress', '14 min', array( 'Self-Hosting', 'Docker', 'Cloudflare' ),
			"<h2>Why self-host</h2>\n<p>Self-hosting is about ownership: your data on your hardware, no per-seat pricing, and no service disappearing and taking your archive with it. It is also the best way to actually learn how the web fits together. The trade is more responsibility for far more control — a good trade for a developer, and the foundation everything else on this site is built on.</p>\n<h2>The hardware</h2>\n<p>You do not need a rack. A small, quiet, low-power machine — a mini PC, an old laptop, or a single-board computer for light loads — is plenty to start. What matters more than raw power is that it runs Linux, stays on, and has enough storage for your data plus backups. Add RAM before you add cores; most self-hosted apps are memory-bound, not CPU-bound.</p>\n<h2>The stack</h2>\n<p>A sane home stack has four layers. A <strong>reverse proxy</strong> (Caddy, Nginx, or Traefik) terminates HTTPS and routes each domain to the right app. Your <strong>apps run in Docker</strong>, isolated from each other and trivially upgradable. A <strong>database</strong> and a cache sit behind them. And <strong>backups</strong> run on a schedule to somewhere off the machine. Keep the configuration portable so the whole thing can move to a VPS later without a rebuild.</p>\n<h2>Exposing it safely — without opening ports</h2>\n<p>The part that scares people is putting a home server on the internet. The modern answer is not to. A <strong>Cloudflare Tunnel</strong> makes an outbound connection from your server to Cloudflare, and traffic flows back down that tunnel — so you never open a port on your router and never expose your residential IP. It also puts Cloudflare's TLS and DDoS protection in front of everything for free. A VPN like Tailscale is the alternative when a service should stay strictly private.</p>\n<h2>Backups and updates</h2>\n<p>Two habits keep self-hosting stress-free. Back up the Docker volumes (your real data) automatically, off-site, and test a restore at least once — an untested backup is a rumour. And update on a cadence: pin image tags, pull, recreate, and roll back to the previous tag if something breaks. Both are boring, and boring is the goal.</p>\n<h2>What to self-host first</h2>\n<p>Start with something low-stakes and genuinely useful so the habit sticks — a bookmarks or notes app, a read-it-later service, or a simple dashboard. Once one app is running behind the proxy with backups and a tunnel, the second and third are the same recipe. From there the ceiling is high: media, analytics, git hosting, and eventually your own knowledge base.</p>",
		),
		array(
			'Getting Started with GitHub',
			'Git and GitHub for real beginners — repos, commits, branches, and pull requests, and the first workflow that sticks.',
			'Published', '9 min', array( 'GitHub', 'Git' ),
			"<h2>Git vs GitHub</h2>\n<p>They are not the same thing. <strong>Git</strong> is the version-control tool that runs on your machine and records the history of your files. <strong>GitHub</strong> is a website that hosts Git repositories and adds collaboration on top — issues, pull requests, reviews, and automation. You can use Git with no GitHub at all; GitHub just gives your history a home online and a way for people to work on it together.</p>\n<h2>The vocabulary that unlocks everything</h2>\n<p>A <strong>repository</strong> (repo) is a project and its full history. A <strong>commit</strong> is a saved snapshot with a message describing what changed. A <strong>branch</strong> is a parallel line of work — you branch off, make commits, and merge back when it is ready. A <strong>pull request</strong> proposes merging one branch into another and is where review and discussion happen. That is 90% of daily use.</p>\n<h2>Your first repository</h2>\n<p>Create a repo on GitHub (with a README), then bring it to your machine and start the loop:</p>\n<pre><code>git clone https://github.com/you/project.git\ncd project\n# edit files...\ngit add .\ngit commit -m \"Describe what changed\"\ngit push</code></pre>\n<p>That is the whole rhythm — <em>clone, edit, add, commit, push</em> — repeated forever. <code>git status</code> tells you where you are; <code>git log --oneline</code> shows the history you are building.</p>\n<h2>Branches and pull requests</h2>\n<p>For anything beyond a trivial change, branch first: <code>git switch -c feature-x</code>, commit your work, <code>git push -u origin feature-x</code>, then open a <strong>pull request</strong> on GitHub. Even working solo this is worth it — the PR is a clean summary of a change, a place for CI to run, and a record of why something was done.</p>\n<h2>README, issues, and good habits</h2>\n<p>A clear <strong>README</strong> is the front door of a repo: what it is, how to run it, and how to contribute. <strong>Issues</strong> track bugs and ideas so they do not live in your head. Write commit messages a future stranger (usually you) can read. Commit small and often; a tidy history is a tool, not a formality.</p>\n<h2>Where to practise</h2>\n<p>GitHub's own free <em>Skills</em> courses walk you through each of these hands-on, inside a real repository — the fastest way to make the vocabulary stick. After that, put a real project up, however small; nothing teaches Git like using it on something you care about.</p>",
		),
		array(
			'Running Local LLMs at Home',
			'What it takes to run large language models on your own hardware — unified memory, the new mini-supercomputers, and the tools that make it painless.',
			'In Progress', '13 min', array( 'AI', 'Self-Hosting' ),
			"<h2>Why run models locally</h2>\n<p>Local inference means privacy (nothing leaves the machine), no per-token bills, no rate limits, and the ability to keep working offline. For anyone building agent systems or a self-hosted knowledge base, having a capable model on your own hardware changes what is possible — you can point tools at it freely without watching a meter.</p>\n<h2>The one number that matters: memory</h2>\n<p>The single biggest constraint on running a model is how much fast memory it fits in. A model's weights, quantized, roughly need their parameter count in gigabytes at 8-bit and about half that at 4-bit — so a 70-billion-parameter model wants ~40GB, and the largest open models want far more. On a traditional PC that memory is the discrete GPU's VRAM, which is expensive and caps out quickly. This is exactly why a new class of hardware is interesting.</p>\n<h2>The new mini-supercomputers</h2>\n<p>Two 2025 arrivals change the maths by giving the GPU access to a huge pool of <strong>unified memory</strong> shared with the system. <strong>NVIDIA's DGX Spark</strong> pairs a Grace-Blackwell superchip with around 128GB of coherent memory, so models that would need several discrete GPUs fit in one small desktop box. <strong>AMD's Strix Halo</strong> (Ryzen AI Max) does something similar for the PC world — a big integrated Radeon GPU with up to 128GB of shared LPDDR5X — landing in machines like the Framework Desktop. Neither replaces a datacentre for training, but both let you <em>run</em> genuinely large models locally, which is what most people actually want.</p>\n<h2>The tools</h2>\n<p>You do not need to write inference code. <strong>Ollama</strong> is the easiest on-ramp — <code>ollama run llama3</code> and you are talking to a model, with a clean API for your own apps. <strong>llama.cpp</strong> is the efficient engine underneath much of the ecosystem and runs almost anywhere. <strong>vLLM</strong> is the choice when you want throughput and to serve many requests. All three sit happily in a container on a home server.</p>\n<h2>Quantization, briefly</h2>\n<p>Quantization shrinks a model by storing its weights at lower precision — 4-bit instead of 16-bit — cutting memory use by roughly four times for a small, usually acceptable quality cost. It is what makes a 70B model fit in a 128GB unified-memory box at all. Start around 4-bit (Q4) and only reach for higher precision if you can measure a difference that matters to you.</p>\n<h2>A realistic setup</h2>\n<p>A capable local stack is unremarkable to run: a Strix Halo mini PC or a DGX Spark, Ollama in Docker behind your reverse proxy, and your agents and tools pointed at its endpoint over the home network. The models do the interesting part; the plumbing is the same self-hosting recipe as everything else on this site.</p>",
		),
	);
	foreach ( $guides as $i => $g ) {
		$id = wp_insert_post( array(
			'post_type'    => 'guide',
			'post_status'  => 'publish',
			'post_title'   => $g[0],
			'post_excerpt' => $g[1],
			'post_content' => $g[5],
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_status', $g[2] );
			update_post_meta( $id, 'dd_read', $g[3] );
			wp_set_object_terms( $id, $g[4], 'tech' );
		}
	}
}

/**
 * Seed Client Work with detailed SAMPLE case studies so the Work archive and
 * single layout are fully populated and ready to duplicate. These are clearly
 * marked demo projects with fictional clients — each body carries a note to
 * replace it with a real project. Nothing here is presented as a genuine
 * engagement.
 */
function dd_seed_client_templates() {
	if ( get_posts( array( 'post_type' => 'client_work', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
		return;
	}

	$note = "\n<hr />\n<p><em>Sample case study — replace with a real project in wp-admin → Client Work.</em></p>";

	$items = array(
		array(
			'title'    => 'Aurora Studio — Portfolio & Booking Site',
			'excerpt'  => 'A fast, bookable portfolio site for a photography studio.',
			'client'   => 'Aurora Studio (sample)',
			'status'   => 'Live',
			'services' => 'Design, WordPress build, Booking integration, SEO',
			'year'     => '2026',
			'tech'     => array( 'WordPress', 'PHP', 'JavaScript' ),
			'body'     => "<h2>The brief</h2>\n<p>A photography studio came in with a site that looked the part but worked against them: it was slow to load on the phones most of their visitors used, it compressed their images into mush, and every booking still turned into a chain of emails to agree on a date. They wanted a portfolio that opened instantly, showed their work at full quality, and let a prospective client request a session in a few taps — without the studio having to babysit an inbox or pay a monthly fee to a booking platform.</p>\n<h2>Approach</h2>\n<p>Before writing any code we agreed on the priorities in order: speed and image quality first, a frictionless booking request second, and easy self-management third. That ordering shaped every later decision — when a nice-to-have threatened the load time, the load time won. Keeping the scope honest up front is what kept the project fast to build and fast to run.</p>\n<h2>What I built</h2>\n<p>A hand-coded WordPress theme with lazy-loaded galleries that serve appropriately sized images per device, so a phone never downloads a desktop-sized photo. Booking is a lightweight request flow wired straight to the studio's own mailbox — no third-party service in the middle taking a cut or holding the data. Structured data was added so individual shoots and the studio itself surface properly in local search, and the whole site is editable in wp-admin, so galleries, packages, and prices change without a developer.</p>\n<ul><li>Sub-second first paint on a mid-range phone, with full-resolution images on demand.</li><li>Self-hosted booking — no per-booking fees and no visitor data leaving the studio.</li><li>Galleries, packages, and availability all editable in wp-admin.</li></ul>\n<h2>Outcome</h2>\n<p>The studio now manages the whole site themselves and takes session requests straight from the page, with the speed and image quality that a photography brand lives or dies by. The email chains are gone, and there is nothing to pay a platform each month to keep it running.</p>",
		),
		array(
			'title'    => 'Meridian Coffee — E-commerce Storefront',
			'excerpt'  => 'A subscription-ready storefront for a specialty coffee roaster.',
			'client'   => 'Meridian Coffee (sample)',
			'status'   => 'Live',
			'services' => 'WooCommerce, Subscriptions, Performance, Hosting',
			'year'     => '2025',
			'tech'     => array( 'WordPress', 'PHP', 'MariaDB', 'Redis' ),
			'body'     => "<h2>The brief</h2>\n<p>A specialty coffee roaster was selling almost entirely through marketplace platforms that took a cut of every bag and kept the customer relationship at arm's length. They wanted to sell directly — both one-off bags and a recurring subscription for regulars — on a storefront they actually owned, fast enough to handle a launch-day rush and simple enough to run without a dedicated store manager.</p>\n<h2>Approach</h2>\n<p>The core tension in any subscription storefront is speed versus dynamism: a cart and a logged-in customer are personal, but most of the page is not. We built around that from the start, caching everything that could safely be cached and keeping the personal parts — cart, account, checkout — always fresh, so the site stays quick under load without ever showing someone the wrong basket.</p>\n<h2>What I built</h2>\n<p>A WooCommerce storefront with a proper subscription flow that lets customers pause, skip, or change a delivery themselves, on top of a self-hosted stack fronted by Cloudflare with Redis handling object caching. Product, pricing, and subscription management all stay in the roaster's hands through the standard WordPress and WooCommerce admin — no bespoke dashboard to learn, and no platform sitting between them and their customers.</p>\n<ul><li>Recurring subscriptions with self-service pause, skip, and swap.</li><li>Aggressively cached, cart-safe pages that stay fast during a launch spike.</li><li>Owned infrastructure — no per-sale platform fees and a direct customer list.</li></ul>\n<h2>Outcome</h2>\n<p>The roaster now takes direct sales and repeat subscription revenue on a storefront they fully control, keeping the margin that the marketplaces used to take and, just as importantly, the relationship with the people who drink their coffee.</p>",
		),
		array(
			'title'    => 'Northwind Labs — SaaS Marketing Site',
			'excerpt'  => 'A marketing site and docs hub for a developer-tools startup.',
			'client'   => 'Northwind Labs (sample)',
			'status'   => 'In Progress',
			'services' => 'Design system, Front-end, Docs, Analytics',
			'year'     => '2026',
			'tech'     => array( 'TypeScript', 'JavaScript', 'PHP' ),
			'body'     => "<h2>The brief</h2>\n<p>A developer-tools startup was preparing for a public beta and needed two things at once: a marketing site that felt technical and trustworthy to an audience of engineers who can smell fluff a mile off, and a documentation hub those same engineers could actually navigate. The two have different jobs — one persuades, one explains — but they had to feel like one product and share a single visual language.</p>\n<h2>Approach</h2>\n<p>Rather than design the marketing pages and the docs separately, we started with a small shared design system — the colours, type, spacing, and components both would draw from — so the marketing site and the documentation would always look and behave like the same thing. Building the system first is slower on day one and much faster after, because every new page is assembled from parts that already exist and already work.</p>\n<h2>What I built</h2>\n<p>A component-driven front end built on that shared design system, and an accessible documentation section with fast client-side search so an engineer can jump to the exact page they need by keyboard alone. Analytics are privacy-respecting — enough to understand what is working without invasive third-party trackers following visitors around, which matters doubly for a company selling to developers who care about exactly that.</p>\n<ul><li>A reusable design system powering both the marketing pages and the docs.</li><li>Keyboard-navigable documentation with fast, in-page search.</li><li>Accessible to WCAG AA, with privacy-respecting analytics and no invasive trackers.</li></ul>\n<h2>Outcome</h2>\n<p>In progress, and on track to launch alongside the product's public beta — a marketing site and a docs hub that read as one coherent product and can grow, page by page, from the same set of building blocks.</p>",
		),
	);

	foreach ( $items as $i => $c ) {
		$id = wp_insert_post( array(
			'post_type'    => 'client_work',
			'post_status'  => 'publish',
			'post_title'   => $c['title'],
			'post_excerpt' => $c['excerpt'],
			'post_content' => $c['body'] . $note,
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_client', $c['client'] );
			update_post_meta( $id, 'dd_status', $c['status'] );
			update_post_meta( $id, 'dd_services', $c['services'] );
			update_post_meta( $id, 'dd_year', $c['year'] );
			wp_set_object_terms( $id, $c['tech'], 'tech' );
		}
	}
}

/**
 * Seed review subjects (honest, from the discovery record).
 */
function dd_seed_reviews() {
	if ( get_posts( array( 'post_type' => 'review', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
		return;
	}
	$reviews = array(
		array(
			'NVIDIA DGX Spark', 'NVIDIA DGX Spark', 'In Progress', '4',
			"<p>A long-form look at NVIDIA's DGX Spark — the desktop-sized personal AI supercomputer built on the GB10 Grace Blackwell superchip — from the perspective of someone who wants to run and fine-tune large models at home without renting cloud GPUs.</p>\n<h2>What it is</h2>\n<p>DGX Spark is NVIDIA's attempt to shrink a DGX-class machine down to something that sits on a desk and runs off a normal wall socket. At its heart is the GB10 Grace Blackwell superchip: a Blackwell-generation GPU joined to a 20-core Arm Grace CPU over NVIDIA's NVLink-C2C interconnect, so the CPU and GPU share one pool of memory instead of copying tensors back and forth across PCIe. NVIDIA quotes up to roughly a petaFLOP of sparse FP4 AI compute, and the headline figure for local work is 128&nbsp;GB of coherent unified memory — the number that decides which models you can actually load.</p>\n<h2>Why the unified memory matters</h2>\n<p>On a conventional workstation the ceiling is the discrete GPU's VRAM; a 24&nbsp;GB card simply cannot hold a large model regardless of how much system RAM you fit. DGX Spark's 128&nbsp;GB of coherent memory is addressable by both the Grace CPU and the Blackwell GPU, so models in the tens of billions of parameters fit locally and inference-time fine-tuning becomes practical on a desktop. NVIDIA positions a single unit around the 200-billion-parameter mark for inference, and two linked over its high-speed networking considerably higher — the pitch is prototyping models at home that would otherwise force you onto rented cloud GPUs.</p>\n<h2>The software story</h2>\n<p>Hardware is only half of it. DGX Spark runs DGX OS — NVIDIA's Ubuntu-based stack — and drops into the same CUDA, container, and framework ecosystem as the cloud DGX machines. For a self-hoster that is the real draw: build and test against the full NVIDIA AI stack locally, then push the same containers to a bigger machine or the cloud without rewriting anything. It is meant to be a development-and-prototyping box, not a datacentre replacement.</p>\n<h2>Where it fits — and where it does not</h2>\n<p>It is aimed squarely at developers, researchers, and AI hobbyists who want serious local inference and light fine-tuning without a rack, the noise, or the power bill of datacentre GPUs. The trade-offs are honest ones: memory bandwidth on a unified LPDDR5X pool is lower than a top-end discrete GPU's dedicated VRAM, so raw token-generation speed on smaller models can trail a big card that happens to fit them — the win is being able to load models a consumer card cannot hold at all. It is also a premium product at a premium price, and for pure training throughput a multi-GPU rig or the cloud still wins.</p>\n<h2>Early verdict</h2>\n<p>DGX Spark is one of the most interesting machines for anyone serious about self-hosted AI: it puts a genuinely large memory pool and the full NVIDIA software stack on a desk, quietly. Whether it earns its price depends on how much you value local iteration and data privacy over raw speed. This review is <strong>in progress</strong> — the rating will firm up once I have run real inference and fine-tuning workloads on one end to end rather than from specifications alone.</p>",
		),
		array(
			'AMD Ryzen AI Max+ (Strix Halo)', 'AMD Ryzen AI Max+ 395 — Strix Halo', 'In Progress', '4',
			"<p>A research-led review of AMD's Strix Halo — the Ryzen AI Max+ platform — the APU that put a large unified-memory, big-iGPU design into mini-PCs and laptops, and quietly became one of the most interesting options for running local LLMs on a budget.</p>\n<h2>What it is</h2>\n<p>Strix Halo is the codename for AMD's Ryzen AI Max and Max+ processors — a big APU that pairs up to 16 Zen 5 CPU cores with an unusually large integrated Radeon GPU (up to 40 RDNA&nbsp;3.5 compute units) and an XDNA&nbsp;2 NPU, all on one package. The trick that matters for AI is the memory: it uses a wide 256-bit LPDDR5X interface and configurations up to 128&nbsp;GB of unified memory shared between CPU, GPU, and NPU, with a large slice assignable to the graphics side as VRAM.</p>\n<h2>Why self-hosters care</h2>\n<p>The same logic as DGX Spark applies at a very different price point. Because the iGPU can be handed a huge share of that 128&nbsp;GB pool, a Strix Halo mini-PC can load models that would never fit on a typical desktop graphics card — 70-billion-parameter-class models become runnable on a small, quiet, low-power box. The wide memory bus gives it far more bandwidth than an ordinary integrated GPU, which is exactly the bottleneck that usually makes iGPUs hopeless for LLM inference. Machines like the Framework Desktop and a wave of mini-PCs are built on it precisely for this reason.</p>\n<h2>The software caveat</h2>\n<p>This is where honesty matters. AMD's ROCm stack has improved a great deal, and llama.cpp with Vulkan or ROCm backends runs well on Strix Halo, but it is still not the friction-free path CUDA is on NVIDIA. Expect to do more configuration, to care which backend you use, and to hit the occasional rough edge that a comparable NVIDIA box would not have. For someone who enjoys owning their stack that is an acceptable tax; for someone who just wants it to work first time, it is a real consideration.</p>\n<h2>Where it fits</h2>\n<p>Strix Halo is the value champion for local AI: dramatically cheaper and lower-power than a DGX-class machine, small enough to sit on a shelf, and capable of loading genuinely large models thanks to its unified memory. It will not match a dedicated datacentre GPU on raw speed, and the software path asks more of you — but as a self-hosted inference box that also happens to be a capable general-purpose PC, it is hard to beat on what you get for the money.</p>\n<h2>Early verdict</h2>\n<p>An outstanding platform for budget-conscious self-hosted AI, with the ROCm maturity gap as the main asterisk. Marked <strong>in progress</strong>: the score is provisional until I have lived with real inference workloads on one and can speak to sustained performance and thermals rather than specifications.</p>",
		),
		array(
			'IntelliJ IDEA Ultimate', 'IntelliJ IDEA Ultimate', 'In Progress', '4',
			"<p>A hands-on review of the Ultimate edition for polyglot, full-stack development — PHP, Python, and web tooling in one IDE.</p>\n<h2>First impressions</h2>\n<p>The all-in-one integration is the draw: databases, HTTP client, and framework support without a pile of plugins.</p>\n<h2>Where it fits</h2>\n<p>Strong for cross-language projects. Full verdict once it has run a few real builds end to end.</p>",
		),
		array(
			'Visual Studio Code', 'Visual Studio Code', 'Planned', '',
			"<p>The open-source editor everyone reaches for — planned review of the extension ecosystem and how far it goes as a full IDE.</p>",
		),
		array(
			'Antigravity', 'Antigravity', 'Planned', '',
			"<p>First impressions of the Antigravity editor — planned once it has had a proper run in a real project.</p>",
		),
		array(
			'Codex', 'Codex', 'Planned', '',
			"<p>Using Codex in a real development loop — planned review focusing on where it helps and where it gets in the way.</p>",
		),
	);
	foreach ( $reviews as $i => $r ) {
		$id = wp_insert_post( array(
			'post_type'    => 'review',
			'post_status'  => 'publish',
			'post_title'   => $r[0],
			'post_excerpt' => sprintf( __( 'A hands-on review of %s.', 'digital-district' ), $r[0] ),
			'post_content' => $r[4],
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_subject', $r[1] );
			update_post_meta( $id, 'dd_status', $r[2] );
			if ( '' !== $r[3] ) {
				update_post_meta( $id, 'dd_rating', $r[3] );
			}
		}
	}
}

/**
 * Seed the owner's honest personal projects (only if none exist yet).
 */
function dd_seed_projects() {
	$existing = get_posts( array( 'post_type' => 'project', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$projects = array(
		array(
			'AI Operating System', 'An agent-driven layer that orchestrates tools, memory, and tasks.', 'In Progress', array( 'AI', 'Agents', 'Systems' ),
			"<h2>Overview</h2>\n<p>The AI Operating System is a personal operating layer that sits above the individual tools I use every day and coordinates them through AI agents. Rather than a single chatbot, the idea is an environment where agents can reach for the right tool, remember what happened before, and carry out multi-step tasks on a schedule — the way an operating system coordinates programs, files, and processes.</p>\n<h2>What it does</h2>\n<p>At its core it manages three things: a catalogue of tools an agent can call, a durable long-term memory that survives between sessions, and a task model that lets work be planned, queued, and picked up later. Agents read from and write to that memory, so context is not lost the moment a conversation ends, and a task started today can be continued tomorrow without re-explaining everything.</p>\n<h2>How it is built</h2>\n<p>The build follows the same principle as the rest of my work: get the core loops right before adding surface features. That means a predictable, well-typed interface for tools, a memory store that is easy to query, and an orchestration loop that is transparent about what it is doing and why. Capabilities are layered on only once that foundation is stable, so the system stays understandable as it grows.</p>\n<h2>Challenges &amp; trade-offs</h2>\n<p>The hardest problem is memory: deciding what an agent should remember, how long to keep it, and how to retrieve the right fragment at the right moment without drowning the model in irrelevant history. Storing everything and searching it later is tempting but makes every decision slow and noisy. The working compromise is a layered memory — a small, always-present working set plus a larger store queried only when needed — which keeps everyday interactions fast while letting older context resurface deliberately. The other trade-off is autonomy versus control: an agent that acts on a schedule is only useful if you can trust and audit what it did, so transparency in the orchestration loop is treated as a feature, not an afterthought.</p>\n<h2>What I would do differently</h2>\n<p>Early on I let capabilities and architecture grow together, and the surface features kept destabilising the foundation underneath. The lesson — now baked into how the project runs — is to freeze and prove the core loops before adding anything visible on top. It feels slower in the moment and pays for itself repeatedly.</p>\n<h2>Status</h2>\n<p>Under active development. The architecture and core loops are the current focus; the more visible agent capabilities come next, once the plumbing underneath them is dependable.</p>",
		),
		array(
			'Self-Hosted Knowledge Hub', 'A private, searchable knowledge base on my own hardware.', 'In Progress', array( 'Self-Hosting', 'Search', 'Docker' ),
			"<h2>Overview</h2>\n<p>The Self-Hosted Knowledge Hub is a private, searchable home for everything I want to keep and find again — notes, documentation, transcripts, and references — running entirely on my own hardware rather than a third-party service. It exists partly to be genuinely useful day to day, and partly to prove out the self-hosting stack that the guides on this site describe.</p>\n<h2>What it does</h2>\n<p>Content is indexed so it can be searched quickly by keyword and, over time, by meaning, so the answer to “where did I write that?” is a couple of keystrokes away instead of a scroll through folders. Because it is self-hosted, nothing leaves my control: no external indexing, no per-seat pricing, and no risk of a service shutting down and taking the archive with it.</p>\n<h2>How it is built</h2>\n<p>It runs on the same portable stack the Home Server Platform provides — Nginx, PHP, a database, and Redis for caching — packaged so it can move between machines without a rebuild, and fronted by a Cloudflare Tunnel so the home IP is never exposed. Keeping it portable is deliberate: the whole point of owning your knowledge base is being able to take it with you.</p>\n<h2>Challenges &amp; trade-offs</h2>\n<p>Search is the interesting engineering problem. Keyword search is fast and cheap but misses anything phrased differently to how you filed it; semantic search finds meaning but costs more to compute and store, and can surface confidently-wrong matches. The plan is to lead with solid keyword search — which is genuinely enough most of the time — and add semantic search as a second layer for the “I know I wrote this somewhere” cases, rather than making every query pay the heavier cost. The other constant tension in self-hosting is backups: a private archive is worthless if a single disk failure erases it, so off-site backups were designed in from the start rather than bolted on after a scare.</p>\n<h2>What I would do differently</h2>\n<p>The first instinct was to over-organise — deep folder hierarchies and elaborate tagging — which turned filing into a chore and discouraged capture. Good search makes rigid structure largely unnecessary, so the current direction favours capturing quickly and finding later over sorting perfectly up front.</p>\n<h2>Status</h2>\n<p>In progress. The ingestion and search foundations are being built first, with richer organisation and semantic search layered on afterwards.</p>",
		),
		array(
			'Interactive Portfolio', 'A cyberpunk portfolio rendered in the browser — this build.', 'In Progress', array( 'WordPress', 'PHP', 'Frontend' ),
			"<h2>Overview</h2>\n<p>This is the project you are looking at: an interactive developer portfolio with a cyberpunk-brutalist identity, built as a hand-coded WordPress theme with no page builder. It is both a place to present work and a case study in its own right — the whole build is documented, and the site doubles as proof that a fast, characterful site does not need a heavy drag-and-drop builder underneath it.</p>\n<h2>What it does</h2>\n<p>It presents personal projects and client work as separate, browsable collections, each with its own detailed case-study page, alongside guides, reviews, and a journal. Projects are imported automatically from GitHub, so the portfolio stays current with the code without manual bookkeeping. An animated neon-city hero, cursor-reactive cards, and a reading-progress bar give it atmosphere, while a fully accessible, server-rendered baseline underneath keeps it usable for everyone.</p>\n<h2>How it is built</h2>\n<p>The theme is plain PHP templates, CSS custom-property design tokens, and a little vanilla JavaScript. Content lives in custom post types edited in wp-admin; the theme only renders it. Fonts are self-hosted, the hero is a dependency-free canvas, and there are no third-party calls or trackers — which also makes it comfortable to run on modest self-hosted infrastructure or Plesk.</p>\n<h2>Challenges &amp; trade-offs</h2>\n<p>The central tension is spectacle versus accessibility. A neon-city hero, cursor-reactive cards, and text that decodes on scroll are exactly the atmosphere the brand wants — but every one of them is a potential barrier for someone on a slow device, a screen reader, or a reduced-motion setting. The rule I held to is that the accessible, server-rendered site is the real product and the effects are progressive enhancement layered on top: the content renders and works with no JavaScript at all, motion respects the system's reduced-motion preference, and nothing decorative is ever the only way to reach information. Making the flashy layer strictly optional is more work than baking it in, and it is the difference between a portfolio that shows off and one that also welcomes everyone.</p>\n<h2>What I would do differently</h2>\n<p>An early version leaned too hard on the text-scramble effect, and on long labels it turned into unreadable noise for a beat too long. Capping the effect to short strings fixed it, but the broader lesson stuck: an effect that degrades legibility, even briefly, has to be constrained hard or dropped. Taste is knowing when the animation is serving the content and when it is fighting it.</p>\n<h2>Status</h2>\n<p>In progress and shipping — this build is live and continuing to gain polish and features.</p>",
		),
		array(
			'Tactical Streaming Interface', 'An overlay and control surface for live streaming.', 'Prototype', array( 'Realtime', 'UI', 'Tooling' ),
			"<h2>Overview</h2>\n<p>The Tactical Streaming Interface is an exploratory prototype for a live-streaming control surface and on-screen overlay — the layer between the streamer and the broadcast that shows live information and provides quick controls without breaking focus.</p>\n<h2>What it explores</h2>\n<p>It is a place to try ideas end to end before committing to a production build: how to lay out live data widgets so they are glanceable, how to bind actions to hotkeys so control is fast, and how to keep an overlay legible over changing video without becoming clutter. Getting those interaction questions right on a small scale is the point of the prototype.</p>\n<h2>Status</h2>\n<p>Prototype. It is deliberately rough — a testing ground for the layout, hotkey, and live-data ideas rather than a finished tool.</p>",
		),
		array(
			'Home Server Platform', 'The self-hosting foundation: Nginx, PHP, MariaDB, Redis, Cloudflare.', 'In Progress', array( 'Self-Hosting', 'Infra', 'Cloudflare' ),
			"<h2>Overview</h2>\n<p>The Home Server Platform is the foundation everything else runs on: a portable, self-hosted Linux stack living on a home server, built so it can move to a VPS later without being rebuilt. It is the practical answer to “own the stack” — the infrastructure that makes the Knowledge Hub, the portfolio, and the rest possible.</p>\n<h2>What it provides</h2>\n<p>It bundles the pieces a modern site needs — Nginx as web server and reverse proxy, PHP and MariaDB for applications, and Redis for caching — configured to be portable rather than tied to any one machine or control panel. A Cloudflare Tunnel fronts all of it, so the residential IP is never exposed and moving to a VPS is a DNS change rather than a migration.</p>\n<h2>How it is built</h2>\n<p>Portability is the design goal, so no host-only assumptions leak into the applications themselves, backups go off-site, and both domains sit behind Cloudflare. The same stack recommendation runs identically at home and on a VPS, which turns the eventual move into an rsync and a DNS switch instead of a rebuild.</p>\n<h2>Challenges &amp; trade-offs</h2>\n<p>Running production-style services on a home connection means confronting two things a datacentre hides from you: a residential IP you should never expose, and an uptime that is only as good as your power and broadband. The Cloudflare Tunnel answers the first — nothing inbound ever touches the home IP, and there are no ports to forward or firewall holes to manage. The second is answered by refusing to depend on the home being special: because the stack is portable and backed up off-site, a prolonged outage is a reason to move to a VPS, not a disaster. Choosing portability over squeezing out every last drop of home-hardware performance is a deliberate trade — it keeps the exit door open at all times.</p>\n<h2>What I would do differently</h2>\n<p>The first setup quietly assumed the home machine in a few places — paths, a service tuned to specific hardware — and those assumptions surfaced painfully the first time I tried to move something. Everything since has been built to be host-agnostic from the outset, which is slightly more disciplined to write and removes an entire category of migration pain later.</p>\n<h2>Status</h2>\n<p>In progress, and already carrying real workloads as the other projects come online.</p>",
		),
		array(
			'AI Research Workspace', 'A workspace for running and comparing AI research experiments.', 'Research', array( 'AI', 'Research' ),
			"<h2>Overview</h2>\n<p>The AI Research Workspace is an environment for running structured AI experiments and comparing their results — a lab bench rather than a product. It exists to make experimentation repeatable: to track what was run, with which inputs, and what came out, so findings are grounded rather than anecdotal.</p>\n<h2>What it explores</h2>\n<p>The focus is on the discipline around experiments — recording runs, prompts, and outputs; comparing approaches on equal footing; and keeping a trail that can be revisited. That structure is what turns scattered tinkering into research you can actually learn from.</p>\n<h2>Status</h2>\n<p>Early research. It is a space for experiments and notes rather than a shipping tool, and it feeds ideas into the AI Operating System as they prove out.</p>",
		),
	);

	foreach ( $projects as $i => $p ) {
		$id = wp_insert_post( array(
			'post_type'    => 'project',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_excerpt' => $p[1],
			'post_content' => $p[4],
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'dd_status', $p[2] );
			wp_set_object_terms( $id, $p[3], 'tech' );
		}
	}
}

/**
 * Seed the blog with honest journal posts and remove WordPress's default
 * "Hello world!" post and "Sample Page". Runs once (skips if real posts exist).
 */
function dd_seed_posts() {
	// Remove the stock placeholders.
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello ) {
		wp_delete_post( $hello->ID, true );
	}
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample ) {
		wp_delete_post( $sample->ID, true );
	}

	// Only seed once.
	$have = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( ! empty( $have ) ) {
		return;
	}

	$posts = array(
		array(
			'Building this portfolio in the open',
			'Journal',
			'<p>This site is being built in public — every decision documented as it happens. The goal is a portfolio that doubles as a case study: how it is made is part of what it shows.</p><h2>The stack</h2><p>A hand-coded WordPress theme, no page builder, running on a self-hosted stack behind Cloudflare and designed to sit happily on Plesk. Everything is editable in wp-admin; the theme just renders it.</p><h2>What is next</h2><p>Written guides on each part of the build, then the video versions. The writing ships first.</p>',
		),
		array(
			'Why I self-host almost everything',
			'Self-Hosting',
			'<p>Owning the stack is the whole point. The same portable stack — Nginx, PHP, MariaDB, Redis — runs at home now and moves to a VPS later without a rebuild, so there is no lock-in and no surprise bills.</p><h2>Hiding the home IP</h2><p>A Cloudflare Tunnel fronts everything, so the residential IP is never exposed and the eventual VPS move is a DNS change, not a migration.</p><h2>The trade-off</h2><p>More responsibility, far more control. For a developer portfolio that is exactly the right trade.</p>',
		),
		array(
			'Going builder-free on WordPress',
			'WordPress',
			'<p>No Oxygen, no Elementor, no drag-and-drop. Just PHP templates, CSS custom properties, and a little vanilla JavaScript. The result is faster, lighter, and completely portable.</p><h2>Custom post types do the work</h2><p>Projects, client work, guides and reviews are custom post types with their own templates. Content lives in the database; the theme renders it. SEO plugins keep full control of titles and meta.</p>',
		),
		array(
			'Pulling my GitHub repos into the site automatically',
			'WordPress',
			'<p>The Projects section imports every public repository straight from GitHub, mapping each repo to a project with a status derived from its activity. A one-click re-sync in wp-admin keeps it fresh, and any write-ups edited by hand are preserved.</p><h2>Honest by default</h2><p>Archived repos read as complete, near-empty ones as planning, active ones as in progress — no manual bookkeeping, no invented status.</p>',
		),
	);

	foreach ( $posts as $i => $p ) {
		$id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_content' => $p[2],
			'post_excerpt' => wp_strip_all_tags( substr( $p[2], 3, 160 ) ),
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i + 1 ) * DAY_IN_SECONDS ),
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			wp_set_object_terms( $id, $p[1], 'category' );
		}
	}
}

/**
 * Build a primary menu with clear, user-friendly labels.
 */
function dd_build_primary_menu() {
	$menu_name = 'Primary';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu->term_id;
		// Don't rebuild if it already has items.
		if ( wp_get_nav_menu_items( $menu_id ) ) {
			return;
		}
	}
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title' => __( 'Home', 'digital-district' ),
		'menu-item-url'   => home_url( '/' ),
		'menu-item-status'=> 'publish',
		'menu-item-type'  => 'custom',
	) );

	// Portfolio group — Projects + Client Work under one dropdown.
	$portfolio_parent = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Portfolio', 'digital-district' ),
		'menu-item-object' => 'project',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Projects', 'digital-district' ),
		'menu-item-object'    => 'project',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $portfolio_parent,
		'menu-item-status'    => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Client Work', 'digital-district' ),
		'menu-item-object'    => 'client_work',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $portfolio_parent,
		'menu-item-status'    => 'publish',
	) );

	// Writing group — Guides + Reviews + Blog under one dropdown.
	$writing_parent = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'  => __( 'Writing', 'digital-district' ),
		'menu-item-object' => 'guide',
		'menu-item-type'   => 'post_type_archive',
		'menu-item-status' => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Guides', 'digital-district' ),
		'menu-item-object'    => 'guide',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $writing_parent,
		'menu-item-status'    => 'publish',
	) );
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => __( 'Reviews', 'digital-district' ),
		'menu-item-object'    => 'review',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-parent-id' => $writing_parent,
		'menu-item-status'    => 'publish',
	) );
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( $blog_id ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'Blog', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $blog_id,
			'menu-item-type'      => 'post_type',
			'menu-item-parent-id' => $writing_parent,
			'menu-item-status'    => 'publish',
		) );
	}
	$about = get_page_by_path( 'about' );
	if ( $about ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'About', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $about->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'Contact', 'digital-district' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $contact->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
