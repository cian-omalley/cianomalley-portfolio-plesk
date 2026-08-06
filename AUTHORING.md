# Writing content: projects, client work, guides, reviews & blog posts

This is the everyday guide to running your site: how to add a project, write up a
client job, publish a guide or review, and post to the blog — all from the free
WordPress editor on Plesk, with **nothing going live until *you* press Publish**.

You don't touch code for any of this. Everything below happens in **wp-admin**
(`https://your-domain/wp-admin`).

---

## The 30-second mental model

- Your site is built from a few **content types**: **Projects**, **Client Work**,
  **Guides**, **Reviews**, and **Blog** posts. Each has its own menu in wp-admin.
- The **theme owns the layout** — the neon styling, the cards, the case-study
  page. You only ever write the **content** (words, images) and fill a small
  **Details** box (status, year, links…). You never fight a page builder for the
  design; it's done for you and stays consistent.
- **Everything you create starts as a _Draft_.** Drafts are invisible to visitors.
  You edit in peace, click **Preview** to see the real page, and only **Publish**
  when it's ready. The theme never publishes anything on your behalf.

> **The "free builder" on Plesk is the built-in WordPress editor** (called the
> Block Editor / Gutenberg). It's free, already installed with WordPress, and is
> all you need. You do **not** need Oxygen, Elementor, or any paid builder.

---

## One-time setup that makes life easier

**Install a free "duplicate" plugin** so you can clone a template in one click:

1. wp-admin → **Plugins → Add New**.
2. Search **“Yoast Duplicate Post”** (free) → **Install** → **Activate**.

Now every project/post list has a **Clone** and **Rewrite & Republish** link when
you hover an item. That's the easiest way to use the templates below. (If you'd
rather not add a plugin, you can copy content by hand — see “Without the plugin”.)

---

## The templates (your starting points)

On activation the theme created one **draft** starter for each type, named:

- `TEMPLATE — New Project (duplicate me)`
- `TEMPLATE — New Client Project (duplicate me)`
- `TEMPLATE — New Guide (duplicate me)`
- `TEMPLATE — New Review (duplicate me)`
- `TEMPLATE — New Blog Post (duplicate me)`

Each one is a ready-made skeleton: the right section headings, a short how-to note
at the top, and the **Details** box pre-filled with example values. They sort to
the **top** of each admin list so they're easy to find.

**Never edit a template directly** — clone it, so it stays clean for next time.

### Make a new item from a template

1. Go to the type you want (e.g. **Projects**).
2. Hover the `TEMPLATE — …` draft → click **Clone**. A copy appears, still a draft.
3. Open the clone. Change the **title**, replace the placeholder text, fill the
   **Details** box, delete the “How to use this template” paragraph.
4. **Save draft** → **Preview** → when happy, **Publish**.

**Without the plugin:** open the template, select all the content
(click in the editor, `Ctrl/Cmd+A`, `Ctrl/Cmd+C`), then **Add New** of that type
and paste (`Ctrl/Cmd+V`). Re-enter the Details values by hand.

---

## Writing the body (the same for every type)

The big editing area is the **block editor**. A few essentials:

- **Type normally** for paragraphs. Press **Enter** for a new paragraph.
- **Headings:** type `##` then space for an H2, `###` for H3 — or click **＋** →
  Heading. Use H2 for the main sections (the theme styles them with the neon look).
- **Lists:** type `- ` (dash space) or `1. ` and it becomes a bullet/number list.
- **Code:** click **＋** → **Code** for a command or snippet block.
- **Links:** select text → click the link icon (or `Ctrl/Cmd+K`) → paste the URL.
- **Bold / italic:** `Ctrl/Cmd+B` / `Ctrl/Cmd+I`.

### Images

- **In the body:** click **＋** → **Image** → **Upload** (drag a file in). This is
  the “upload” step — images live in your **Media Library**, not in the theme.
- **Featured image** (the card thumbnail + the big cover on the page): in the right
  sidebar open **Featured image → Set featured image**. If you skip it, the theme
  draws a tidy neon placeholder automatically, so it's optional.

### The excerpt (the card summary)

The one-line text on the card comes from the **Excerpt**. Right sidebar →
**Excerpt** (Posts) or it's built into the Details area. Keep it to a sentence.

---

## Photos, diagrams and videos

You can put images, diagrams and videos anywhere in a project or blog post's body.
Everything is styled to sit neatly inside the reading column and scale down on
phones automatically — you don't set any sizes.

### Photos & diagrams
- In the editor, click **＋** → **Image** → **Upload**, or just **drag the file**
  into the editor. It's stored in your **Media Library**.
- Add a **caption** in the small text line under the image if you want one.
- **Diagrams** are just images — export your diagram as **PNG or JPG** and upload
  it the same way. A screenshot works too.
- **SVG diagrams:** WordPress blocks SVG uploads by default (a safety measure). If
  you want crisp vector diagrams, either **export them to PNG**, or install the free
  **“Safe SVG”** plugin (Plugins → Add New) which sanitises and allows SVGs.
- Want several photos together? Use the **Gallery** block (**＋** → Gallery) — it
  lays them out in a responsive grid.

### Videos
Two ways, both fully responsive (16:9):

1. **YouTube / Vimeo (recommended):** click **＋** → **YouTube** (or **Video →
   Embed**), paste the video URL, press Enter. It becomes a responsive player.
   *(On first paste WordPress fetches the preview from the provider, so the server
   needs outbound internet — normal on any Plesk host.)*
2. **Your own video file:** **＋** → **Video** → **Upload** an `.mp4`. It gets
   native playback controls. Keep self-hosted files small (large videos eat disk and
   bandwidth) — for anything long, YouTube/Vimeo is the better choice.

### The featured image vs. body images
- The **Featured image** (right sidebar) is the card thumbnail and the big cover at
  the top of the page — set one per project/post for the best look.
- **Body images/videos** are the ones you place inside the writing, as above.

---

## The Details box, field by field

Every type except the blog has a small **Details** box (right-hand side). Only the
fields that matter for that type appear.

### Project — *Projects → Add New*
| Field | What it does |
| --- | --- |
| **Status** | Badge on the card/page: In Progress · Planning · Complete · Prototype · Research · Live |
| **My role** | e.g. “Designer & developer” (optional) |
| **Year** | e.g. 2026 |
| **Repository URL** | Public GitHub link → adds a **View code** button. Leave blank for private repos (a “Private repository” note shows instead). |
| **Live URL** | A demo/live link → adds a **Visit live site** button |
| **Stack (tags)** | The “Tech” box: type a technology and press Enter (PHP, Go, WordPress…) — shown as tags and in the page's Stack line |

### Client Work — *Client Work → Add New*
| Field | What it does |
| --- | --- |
| **Client** | Client name (write “(sample)” if it's a demo) |
| **Status** | Live · In Progress · Complete · Prototype |
| **Services provided** | e.g. “Design, WordPress build, SEO” |
| **Year** | e.g. 2026 |
| **Live URL** | Link to the delivered site |
| **Stack (tags)** | Same “Tech” tags as projects |

### Guide — *Guides → Add New*
| Field | What it does |
| --- | --- |
| **Status** | Published · In Progress · Planned |
| **Read time** | e.g. “8 min” |
| **Stack (tags)** | Topic tags (Docker, Self-Hosting, GitHub…) |

### Review — *Reviews → Add New*
| Field | What it does |
| --- | --- |
| **Subject** | What you're reviewing (e.g. “NVIDIA DGX Spark”) |
| **Rating** | 1–5 → shown as stars |
| **Verdict** | Recommended · Mixed · Not Recommended · In Progress · Planned |
| **Link** | Where to find the thing reviewed |
| **Stack (tags)** | Topic tags |

### Blog post — *Posts → Add New*
No Details box — just **title**, **body**, **Excerpt**, a **Featured image**, and
**Categories/Tags** in the right sidebar. That's a normal WordPress post.

---

## The publish workflow (every time)

1. **Save draft** while you write (top-right).
2. **Preview → Preview in new tab** to see the real, styled page.
3. When ready, **Publish** (top-right) → confirm. It's now live at its own URL and
   appears on the homepage and in its archive (`/projects/`, `/work/`, `/guides/`,
   `/reviews/`, `/blog/`).
4. Changed your mind after publishing? Open it → **Switch to draft** (next to the
   Publish/Update button) to pull it offline again, or move it to **Trash**.

---

## “Make sure nothing is published yet” on an existing site

If you activated the theme earlier and some example content is already **live**,
take it all offline in one go:

1. Go to **Projects** (repeat for Client Work, Guides, Reviews, Posts).
2. Tick the **checkbox in the header** to select everything on the page.
3. **Bulk actions → Edit → Apply**, set **Status → Draft**, **Update**.

Now nothing is public until you publish it yourself, item by item. (Fresh
activations already start fully drafted — this is only for a site set up before.)

---

## Adding a page to the navigation (optional)

The top menu is built automatically (Home · Portfolio · Writing · About · Contact).
To add or reorder items: **Appearance → Menus**, pick the **Primary** menu, add
pages/links, drag to order, **Save**.

---

## Quick reference

| I want to… | Go to |
| --- | --- |
| Add a software/personal project | **Projects → Add New** (or clone the template) |
| Write up a client job | **Client Work → Add New** |
| Publish a how-to / tutorial | **Guides → Add New** |
| Review a tool/product | **Reviews → Add New** |
| Post to the blog | **Posts → Add New** |
| Change the homepage intro/title | **Settings → General** (title & tagline) |
| Edit the About page | **Pages → About** |
| Re-import public repos from GitHub | **Projects → Sync GitHub** |
| Take something offline | Open it → **Switch to draft** |

That's the whole system. Clone a template, write, preview, publish — and the design
takes care of itself.
