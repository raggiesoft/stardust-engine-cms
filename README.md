# The Stardust Engine CMS

![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)
![Release](https://img.shields.io/badge/Release-v1.0.0-success.svg)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.8-7952B3.svg)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla_SPA-F7DF1E.svg)
![Accessibility](https://img.shields.io/badge/WCAG-2.1_AA-005A9C.svg)

A zero-database, flat-file Content Management System paired with **Elara**, a lightweight Vanilla JS Single Page Application (SPA) router. Built for independent creators, musicians, and systems architects who want sub-millisecond server-side rendering (SSR) and persistent media playback without relational database overhead or heavy frontend framework lock-in.

---

## ⚡ Quickstart (Run Locally in 30 Seconds)

The repository includes pre-configured defaults (`data/settings.json`, `data/routes/routes.json`, and a local sample audio album in `assets/json/sample-album/`) so you can clone and run the CMS immediately with **zero external dependencies or paid API tokens**:

```bash
git clone https://github.com/raggiesoft/stardust-engine-cms.git
cd stardust-engine-cms
php -S localhost:8000 router.php
```

Open **`http://localhost:8000`** in your browser to explore the live interactive demos:
- **`/`** — Landing page & interactive architecture cards
- **`/project`** — Hierarchical JSON route inheritance (`common` block) with contextual sidebar
- **`/project/about`** — Child route layout override (`"sidebar": false` full-width view)
- **`/project/secret-page`** — Decoupled route mapping (`/project/secret-page` $\rightarrow$ `pages/projects/literally-any-name.php`)
- **`/project/dynamic-directory`** — `$_GET` query string parameter routing (`?id=alpha`) across soft SPA transitions
- **`/project/album-releases`** — Persistent **Stardust Audio Player**, Markdown lyrics modal, DSP store selector, and Schema.org JSON-LD injection
- **`/project/radio`** — Multi-catalog JSON playlist aggregator with ISRC deduplication and smart album shuffling

---

## 🚀 The Philosophy: Empathetic Engineering

The Stardust Engine is built on the conviction that resilient software must respect both server infrastructure and the user's cognitive load:
1. **Zero-Database Architecture**: 100% flat-file JSON and Markdown data stores read natively via PHP 8.5 arrays. Zero SQL connection pools, zero ORM overhead, and near-instant Time-to-First-Byte (TTFB).
2. **Elara Vanilla JS SPA Router (`assets/js/elara-spa.js`)**: Intercepts internal `<a>` clicks, fetches the next view asynchronously, diffs `<head>` stylesheets/themes, and swaps `#elara-layout-wrapper` and `#elara-master-footer` while keeping `#global-player-zone` untouched—allowing continuous audio playback across page navigations.
3. **Strict WCAG 2.1 AA / Section 508 Compliance**: Every template and CSS variable in `assets/css/root.css` is calibrated for $\ge 4.5:1$ contrast across both Light Mode (`[data-bs-theme="light"]`) and Dark Mode (`[data-bs-theme="dark"]` / `@media (prefers-color-scheme: dark)`), with automatic `#main-content` focus transfer after every SPA transition.

---

## 🗺️ Directory Structure

```text
stardust-engine-cms/
├── [RENAME_THIS_DIR]/            # Production front-controller directory (rename on deploy)
│   ├── [RENAME_THIS_ROUTER].php  # Core PHP 8.5 JSON route resolver & layout engine
│   └── errors/                   # 403, 404, 500, 502, 503, 504 fallback views
├── assets/                       # Public static assets
│   ├── css/
│   │   ├── root.css              # WCAG 2.1 AA Light & Dark Mode CSS variables
│   │   ├── safety-net.css        # Typography and dark-mode contrast fallbacks
│   │   ├── header.css            # Adaptive navbar theming
│   │   ├── footer.css            # Adaptive footer theming
│   │   └── extras.css            # Glass HUD panels (.bg-hud-*), glows, and .hover-lift
│   ├── images/
│   │   └── og-default.svg        # Default OpenGraph social preview banner
│   ├── js/
│   │   └── elara-spa.js          # Elara Vanilla JS SPA Router & Head Sync Engine
│   └── json/
│       └── sample-album/         # Local sample album (album.json, tracks.json, SVG art, .md lyrics)
├── data/
│   ├── settings.json             # Active global configuration
│   ├── settings.example.json     # Template global configuration
│   └── routes/
│       ├── routes.json           # Active hierarchical URL route map
│       └── route.example.json    # Template URL route map
├── devops/                       # Optional CI/CD & CDN sync automation templates
│   ├── jenna-sync.example.sh     # Local Git push + rclone CDN asset sync script
│   └── sarah-deploy.example.sh   # Server-side cron rsync deployment script
├── docs/
│   └── ai-persona.md             # AI assistant architecture troubleshooting prompt
├── includes/
│   ├── header.php                # Global HTML <head>, OpenGraph tags, Bootstrap 5.3.8 CDN, & <header>
│   ├── footer.php                # Global <footer> wrapper, module loader, & delegated theme toggle
│   ├── components/
│   │   ├── headers/              # Navbar menu components (header-default.php)
│   │   ├── footers/              # Visual footer components (footer-default.php)
│   │   ├── sidebars/             # Contextual sidebar components (sidebar-default.php)
│   │   ├── store-button.php      # Persistent DSP streaming & physical merch dropdown
│   │   ├── _tracklist-downloader.php # Schema.org album tracklist & player dispatcher
│   │   └── 3rdparty/parsedown/   # Bundled Parsedown Markdown parser
│   └── modules/
│       └── stardust-player/      # Persistent floating SPA audio player & Markdown lyrics modal
├── pages/                        # Physical PHP view fragments
│   ├── home.php                  # Root landing page (/)
│   └── projects/                 # Demo views (/project/*)
├── router.php                    # Built-in local dev router (`php -S localhost:8000 router.php`)
├── llms.txt                      # AI coding assistant architectural guardrails
├── nginx.conf.example            # Production Nginx server block template
├── LICENSE                       # MIT License
└── README.md
```

---

## 🔒 Production Deployment (Nginx + PHP 8.5-FPM)

When deploying to a production Nginx server:
1. **Rename the Router Directory & File**: For security-in-depth against automated bot scanners, rename `[RENAME_THIS_DIR]` and `[RENAME_THIS_ROUTER].php` to your own custom names (for example, `_core/engine.php`).
2. **Configure Nginx**: Copy `nginx.conf.example` to `/etc/nginx/sites-available/your-site.conf` and update the `index`, `try_files`, and `error_page` directives to match your renamed directory and router file:
   ```nginx
   location / {
       try_files $uri $uri/ /_core/engine.php?$query_string;
   }
   ```
3. **Optional Custom Icon Kits**: Out of the box, `includes/header.php` loads **Bootstrap 5.3.8**, **Bootstrap Icons 1.11.3**, and **Font Awesome Free 6.7.2** from public CDNs. If you have a custom Font Awesome Pro or Web Awesome kit URL, add it to `fontAwesomeKit` or `webAwesomeKit` in `data/settings.json`.

---

## 📄 License

Released under the **MIT License**. See [`LICENSE`](LICENSE) for full details.
```

---

## 🔒 Production Deployment (Nginx + PHP 8.5-FPM)

When deploying to a production Nginx server:
1. **Rename the Router Directory & File**: For security-in-depth against automated bot scanners, rename `[RENAME_THIS_DIR]` and `[RENAME_THIS_ROUTER].php` to your own custom names (for example, `_core/engine.php`).
2. **Configure Nginx**: Copy `nginx.conf.example` to `/etc/nginx/sites-available/your-site.conf` and update the `index`, `try_files`, and `error_page` directives to match your renamed directory and router file:
   ```nginx
   location / {
       try_files $uri $uri/ /_core/engine.php?$query_string;
   }
   ```
3. **Optional Custom Icon Kits**: Out of the box, `includes/header.php` loads **Bootstrap 5.3.8**, **Bootstrap Icons 1.11.3**, and **Font Awesome Free 6.7.2** from public CDNs. If you have a custom Font Awesome Pro or Web Awesome kit URL, add it to `fontAwesomeKit` or `webAwesomeKit` in `data/settings.json`.

---

## 📄 License

Released under the **MIT License**. See [`LICENSE`](LICENSE) for full details.
└── README.md