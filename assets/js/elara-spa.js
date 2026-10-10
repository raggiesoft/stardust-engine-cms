/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: elara-spa.js
 * =========================================================================================
 * RaggieSoft Elara SPA Router (Vanilla JS)
 * This script serves as the core navigation engine for the Stardust Engine CMS. It acts as
 * a lightweight, vanilla JavaScript replacement for libraries like Turbo or Pjax, enabling
 * instantaneous "soft" page transitions without full browser reloads.
 * 
 * CORE RESPONSIBILITIES:
 * 1. Event Interception: Hijacks all compliant `<a>` link clicks to prevent hard reloads.
 * 2. DOM Swapping: Fetches target URLs via AJAX, parses the response, and atomically swaps
 *    specific "zones" (e.g., `#elara-layout-wrapper`) while keeping the shell intact.
 * 3. Asset Synchronization: Diffs and synchronizes `<head>` elements (stylesheets, meta tags)
 *    to ensure proper visual rendering for the newly loaded view.
 * 4. State Management: Integrates with the HTML5 History API (pushState/popstate) to 
 *    maintain correct back/forward button behavior.
 * 
 * FUTURE MAINTENANCE NOTES:
 * - If you modify the core page layout IDs in the templates (e.g., `#elara-layout-wrapper`),
 *   you MUST update the `swapZones` array in this file.
 * - The `scrollRestoration` logic and timing timeouts (15ms) are highly sensitive. Altering 
 *   these may reintroduce scroll jumping or flash-of-unstyled-content (FOUC) artifacts.
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// Initialization: Scroll Management
// -----------------------------------------------------------------------------------------
// Tell the browser to yield control of scroll positions to Elara. This prevents the browser
// from abruptly jumping to previous scroll coordinates while the old DOM is still rendering.
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------------------------------
    // 1. Intercept All Link Clicks
    // -------------------------------------------------------------------------------------
    // Bind a global click listener to the body to catch all current and future injected links.
    document.body.addEventListener('click', async (e) => {
        // Look up the DOM tree to find the closest anchor tag
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');

        // Condition 1: Let Bootstrap Native JS handle its own interactive components
        // (e.g., dropdowns, modals, tabs)
        if (link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-bs-dismiss')) {
            // Prevent the browser from jumping to the anchor hash and breaking the SPA state
            if (href && href.startsWith('#')) e.preventDefault();
            return; 
        }
        
        // Condition 2: Ignore dead links and specialized utility protocols (mail/phone)
        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            if (href === '#') e.preventDefault();
            return;
        }

        // Condition 3: Ignore links intending to open in new tabs or modified via keyboard
        if (link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;

        // Resolve absolute paths for consistent comparison
        const targetUrl = new URL(link.href, window.location.href);
        const currentUrl = new URL(window.location.href);

        // Condition 4: Ignore external links pointing outside the current domain
        if (targetUrl.origin !== currentUrl.origin) return;
        
        // Condition 5: Robustly ignore same-page anchor hash links
        // If the path is identical and an anchor is specified, let the browser handle it natively.
        if (targetUrl.pathname === currentUrl.pathname) {
            if (targetUrl.hash !== '' || link.href.endsWith('#')) {
                return; 
            }
        }

        // --- All checks passed: Elara takes over ---
        
        // Prevent the standard browser hard reload
        e.preventDefault();
        
        // Execute the asynchronous soft navigation sequence
        await navigateTo(targetUrl.href);
    });


    // -------------------------------------------------------------------------------------
    // 2. Handle Browser Back/Forward Buttons
    // -------------------------------------------------------------------------------------
    window.addEventListener('popstate', async (e) => {
        // Re-run the soft navigation logic when the user utilizes history buttons.
        // Pass 'false' to pushState to prevent duplicating the history entry.
        await navigateTo(window.location.href, false);
    });
});

/**
 * -----------------------------------------------------------------------------------------
 * Core Navigation Engine
 * -----------------------------------------------------------------------------------------
 * Performs the AJAX fetch, DOM parsing, state management, and visual rendering updates.
 * 
 * @param {string} url - The target destination URL to fetch.
 * @param {boolean} pushState - Whether to record this transition in browser history.
 */
async function navigateTo(url, pushState = true) {
    // Fire global event to trigger UI loader animations (e.g., #page-loader)
    document.dispatchEvent(new CustomEvent('elara:navigating'));

    try {
        // Fetch the raw HTML string of the target page
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const htmlString = await response.text();

        // Convert the raw string into a queryable DOM tree in memory
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlString, 'text/html');

        // Extract metadata and verify the target page is an Elara-compatible layout
        const newTitle = doc.querySelector('title')?.innerText;
        let hasCoreLayout = doc.querySelector('#elara-layout-wrapper');

        if (hasCoreLayout) {
            // =============================================================================
            // --- Phase 1: HEAD & META SYNC ENGINE ---
            // =============================================================================

            // Extract DOM attributes to synchronize forced themes (`data-bs-theme`, classes)
            const newHtmlAttrs = Array.from(doc.documentElement.attributes);
            const currentHtmlAttrs = Array.from(document.documentElement.attributes);

            // Step 1.1: Purge stale attributes that exist on the current DOM but NOT on the new page
            currentHtmlAttrs.forEach(attr => {
                if (!doc.documentElement.hasAttribute(attr.name)) {
                    document.documentElement.removeAttribute(attr.name);
                }
            });

            // Step 1.2: Add or update attributes mapped from the incoming page
            newHtmlAttrs.forEach(attr => {
                document.documentElement.setAttribute(attr.name, attr.value);
            });

            // Step 1.3: THE SYSTEM RESTORE
            // If the new page has no forced theme, re-apply the user's OS or localized preference
            if (!doc.documentElement.hasAttribute('data-bs-theme')) {
                const storedTheme = localStorage.getItem('theme');
                const preferredTheme = storedTheme ? storedTheme : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', preferredTheme);
            }

            // Step 1.4: Diff and Update External Stylesheets
            // Utility to extract base URLs, ignoring cache-busting `?v=` timestamps
            const getBaseHref = (link) => link.href.split('?')[0]; 
            const newLinks = Array.from(doc.querySelectorAll('link[rel="stylesheet"]'));
            const oldLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));

            // Append newly discovered stylesheets to the head
            newLinks.forEach(newLink => {
                if (!oldLinks.some(old => getBaseHref(old) === getBaseHref(newLink))) {
                    document.head.appendChild(newLink.cloneNode(true));
                }
            });

            // Remove obsolete stylesheets no longer required by the incoming page
            oldLinks.forEach(oldLink => {
                if (!newLinks.some(newEl => getBaseHref(newEl) === getBaseHref(oldLink))) {
                    oldLink.remove();
                }
            });

            // Step 1.5: Update Inline Styles 
            // Specifically diffing head-level styles to avoid touching inline body elements
            const newHeadStyles = Array.from(doc.head.querySelectorAll('style'));
            const oldHeadStyles = Array.from(document.head.querySelectorAll('style'));

            oldHeadStyles.forEach(style => style.remove());
            newHeadStyles.forEach(style => document.head.appendChild(style.cloneNode(true)));


            // =============================================================================
            // --- Phase 2: LOADER STATE UPDATE & PRE-SCROLL LOCK ---
            // =============================================================================
            
            // Silently sync the preloader text from the new DOM so the *next* click reflects it correctly
            const newLoaderTitle = doc.querySelector('#page-loader h4');
            const oldLoaderTitle = document.querySelector('#page-loader h4');
            if (newLoaderTitle && oldLoaderTitle) {
                oldLoaderTitle.innerHTML = newLoaderTitle.innerHTML;
            }

            // Assassinate smooth scrolling temporarily BEFORE the DOM height changes
            // This prevents the browser from aggressively animating upward
            document.documentElement.style.scrollBehavior = 'auto';

            // Instantly snap scroll axes to zero while the old (potentially taller) DOM is still intact
            window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;


            // =============================================================================
            // --- Phase 3: DOM ZONE SWAPPING ---
            // =============================================================================
            
            // Define the isolated architectural blocks to be updated atomically
            const swapZones = [
                'header',                    
                '#elara-layout-wrapper',     
                '#elara-master-footer'   
            ];

            swapZones.forEach(selector => {
                const newEl = doc.querySelector(selector);
                const currentEl = document.querySelector(selector);
                
                if (newEl && currentEl) {
                    // Execute the primary DOM replacement
                    currentEl.replaceWith(newEl);

                    // Hack/Fix: `innerHTML` replacement does not execute `<script>` tags natively.
                    // We must locate newly injected scripts, clone them, and re-append them to trigger execution.
                    // This ensures plugins like the audio player or custom page scripts fire correctly.
                    const newlyInjectedEl = document.querySelector(selector);
                    const scripts = newlyInjectedEl.querySelectorAll('script');
                    
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        // Copy all attributes (src, async, defer, type)
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        // Copy inline script contents
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        // Execute by replacing the dormant script with the active clone
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                }
            });

            // Update the browser tab title and URL bar
            if (newTitle) document.title = newTitle;
            if (pushState) window.history.pushState({ url: url }, newTitle, url);

            // =============================================================================
            // --- Phase 4: POST-SCROLL ENFORCEMENT & FOCUS ---
            // =============================================================================
            
            // Force a synchronous layout calculation so the browser knows the exact height 
            // of the newly injected DOM before we run our final scroll logic.
            void document.documentElement.offsetHeight;

            // A tiny timeout (15ms) ensures the main thread's render queue has fully cleared
            setTimeout(() => {
                // Re-enforce the 0,0 scroll axes just in case the browser tried to jump during render
                window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
                document.documentElement.scrollTop = 0;
                document.body.scrollTop = 0;

                // THE ANCHOR: Physically move the browser's active focus to the main content area.
                // This is critical for screen reader accessibility after an SPA transition.
                const mainContent = document.getElementById('main-content') || document.body;
                mainContent.setAttribute('tabindex', '-1');
                mainContent.focus({ preventScroll: true }); 
                // Clean up the tabindex if it was applied to the body to avoid outline issues
                if (mainContent === document.body) mainContent.removeAttribute('tabindex');

                // Resurrect smooth scrolling capabilities for the user
                document.documentElement.style.scrollBehavior = '';

                // Dispatch global event signifying the soft-load sequence has completely finished
                document.dispatchEvent(new CustomEvent('elara:loaded'));
            }, 15);

        } else {
            // Fallback: If the target page is missing the core layout ID, it's not an Elara page.
            // Abort the SPA routine and execute a standard browser hard redirect.
            window.location.href = url;
        }
    } catch (error) {
        // Fallback: On any network or parsing failure, fail gracefully to a hard redirect.
        console.error('Elara SPA Error:', error);
        window.location.href = url;
    }
}

/**
 * -----------------------------------------------------------------------------------------
 * Elara Secure Mail Obfuscator
 * -----------------------------------------------------------------------------------------
 * Re-assembles securely fragmented email components (user, domain, TLD) into standard 
 * `mailto:` anchor tags. This prevents simple bot scrapers from harvesting email addresses.
 */
function initializeSecureEmails() {
    document.querySelectorAll('.elara-secure-mail').forEach(link => {
        // State lock: Prevent double-binding if the link persists across SPA transitions
        if (link.dataset.secured === "true") return;
        
        const user = link.getAttribute('data-u');
        const domain = link.getAttribute('data-d');
        const tld = link.getAttribute('data-t');
        
        if (user && domain && tld) {
            // Assemble the complete email string in memory
            const email = `${user}@${domain}.${tld}`;
            
            // Inject the functional mailto protocol into the href attribute
            link.setAttribute('href', `mailto:${email}`);
            
            // Mark the element as processed to flag the state lock
            link.dataset.secured = "true";
        }
    });
}

// Ensure the secure email logic runs on the initial hard page load
document.addEventListener('DOMContentLoaded', initializeSecureEmails);

// Ensure the secure email logic re-evaluates the DOM every time Elara fetches a new page
document.addEventListener('elara:loaded', initializeSecureEmails);