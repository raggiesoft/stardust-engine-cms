# The Stardust Engine CMS

![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)
![Release](https://img.shields.io/badge/Release-v0.1.0-success.svg)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg)
![Ubuntu](https://img.shields.io/badge/Ubuntu-26.04_LTS-E95420.svg)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E.svg)
![Accessibility](https://img.shields.io/badge/WCAG-2.1_AA-005A9C.svg)

A zero-database, flat-file content management system paired with a blazingly fast Vanilla JS routing engine. Built for independent creators and architects who want enterprise-level speed without heavy framework lock-in.

## 🚀 The Philosophy: Empathetic Engineering
The Stardust Engine is built on the conviction that resilient software must respect both the server's infrastructure and the user's cognitive load. By eliminating SQL database latency and utilizing an intelligent Single Page Application (SPA) router, this CMS delivers native-app speeds while maintaining strict adherence to web accessibility standards.

## 🏗️ Core Architecture

### 1. The Engine (PHP 8.5 & Nginx)
A strictly structural MVC routing layer optimized for modern server environments.
* **Native Infrastructure:** Engineered specifically for Ubuntu 26.04 LTS, utilizing native PHP 8.5 packages for enhanced security and stability without relying on third-party PPAs.
* **Zero-Database:** Entirely flat-file architecture driven by PHP array manipulation and JSON data stores, resulting in incredibly low Time-to-First-Byte (TTFB).

### 2. Elara: The SPA Router
A custom Vanilla JS engine that intercepts standard `<a href>` clicks and selectively swaps DOM nodes (`header`, `#elara-layout-wrapper`, `footer`) without a hard page refresh.
* **Dynamic Theme Sync:** Automatically detects and diffs `<head>` attributes to seamlessly transition between Light and Dark modes.
* **Ghost DOM Protection:** Assumes element destruction on navigation, enforcing global event delegation for extreme client-side stability.

### 3. "Personified" CI/CD Pipeline (Jenna & Sarah)
Included bash automation scripts for zero-downtime atomic deployments and semantic versioning.
* **Jenna (Local):** Handles local packaging, generates sitemaps, accepts semantic release tags (e.g., `v0.1.0`), and acts as a strict QA gatekeeper.
* **Sarah (Server):** Quietly pulls updates via `rsync` for seamless asset synchronization without ever requiring root server permissions.

## 🗺️ Directory Structure

```text
stardust-engine-cms/
├── [RENAME_THIS_DIR]/            # The obfuscated core engine
│   ├── [RENAME_THIS_ROUTER].php  # The primary router (elara.php)
│   └── errors/                   # 404, 500, 503 fallback views
├── assets/                       # Static files (Served natively via Nginx)
│   ├── css/
│   │   ├── root.css              # Global variables and color palettes
│   │   ├── safety-net.css        # Typography and contrast fallbacks
│   │   └── extras.css            # Glass-morphism and HUD UI components
│   ├── js/
│   │   └── elara-spa.js          # The Vanilla JS SPA Router
│   └── images/
├── data/
│   ├── settings.example.json     # Global site variables
│   └── routes/
│       └── routes.example.json   # Hierarchical URL mapping
├── devops/                       # Personified CI/CD Pipeline
│   ├── jenna-sync.example.sh     # Local push/tag/sync automation
│   └── sarah-deploy.example.sh   # Server-side autonomous pull/sync
├── docs/
│   └── ai-persona.md             # AI prompt instructions with Holly
├── includes/
│   ├── components/               # Headers, footers, and sidebars
│   └── modules/                  # Self-contained logic blocks (e.g. audio players)
├── pages/                        # The physical PHP view fragments
├── llms.txt                      # AI Architecture constraints
├── nginx.conf.example            # Required server block configurations
├── LICENSE
└── README.md