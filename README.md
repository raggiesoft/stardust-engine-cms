# The Stardust Engine CMS

![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)
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
* **Zero-Database:** Entirely flat-file architecture driven by PHP 8.5 arrays and includes.
* **Centralized Configuration:** Uses JSON routing maps to determine layouts, sidebars, and SEO schema injection, keeping the codebase DRY and maintainable.
* **Edge-Cache Synergy:** Pairs perfectly with Nginx routing and Cloudflare caching. The server seamlessly hands off static rendered HTML, drastically reducing origin load.

### 2. Elara SPA Router (Frontend)
A highly optimized Vanilla JavaScript listener that intercepts internal navigation.
* **Seamless Transitions:** Aborts hard refreshes, dynamically diffs the `<head>`, swaps stylesheets, and updates the `<body>` elements for instant, fluid page loads.
* **Persistent Audio:** Audio and video elements continue playing uninterrupted across state changes, ideal for multimedia distribution and continuous playback.
* **Scraper Immunity:** Features a native email obfuscation pipeline using HTML data-attributes, rendering contact details invisible to automated bots while remaining fully functional for actual users.

### 3. CI/CD Pipeline (Jenna & Sarah)
Included bash automation scripts for zero-downtime atomic deployments.
* **Jenna (Local):** Handles local packaging and acts as a strict QA gatekeeper, enforcing WCAG accessibility checks before code can be pushed.
* **Sarah (Server):** Quietly pulls updates via `rsync` for seamless asset synchronization without ever requiring root server permissions.

## ♿ Accessibility First
This system does not just patch accessibility issues; it natively embeds them. The continuous integration scripts are designed to explicitly fail if required ARIA attributes or `alt` text configurations are missing. This guarantees universal navigability for screen readers and keyboard-only users right out of the box.

## 💻 Installation & Quick Start
*(Documentation on generic boilerplate setup, Nginx configuration, and local environment initialization coming soon.)*

## 📄 License
This project is open-source software licensed under the [MIT License](LICENSE). **This framework is not, and will never be, available as a commercially licensed option.**

---

## 👨‍💻 About the Architect
**Built and maintained by Michael P. Ragsdale.**

I am a professional systems architect and full-stack developer based in Norfolk, Virginia. I specialize in custom web infrastructure, strict accessibility compliance, and high-performance server configurations. 

*Please note: I exclusively accept direct W-2 employment opportunities. I do not accept 1099 contract or vendor roles.*