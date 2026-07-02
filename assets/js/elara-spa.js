/**
 * RaggieSoft Elara SPA Router (Vanilla JS)
 * Replaces Turbo for lightweight, native page transitions.
 */

// Tell the browser to let Elara handle scroll positions natively
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Intercept all link clicks
    document.body.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');

        // 1. Let Bootstrap Native JS handle its own components
        if (link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-bs-dismiss')) {
            // Prevent the browser from jumping to the anchor hash
            if (href && href.startsWith('#')) e.preventDefault();
            return; 
        }
        
        // 2. Ignore dead links and utility protocols
        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            if (href === '#') e.preventDefault();
            return;
        }

        // 3. Ignore new tabs or modifier-key clicks
        if (link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;

        const targetUrl = new URL(link.href, window.location.href);
        const currentUrl = new URL(window.location.href);

        // 4. Ignore external links
        if (targetUrl.origin !== currentUrl.origin) return;
        
        // 5. Robustly ignore same-page anchor hash links
        if (targetUrl.pathname === currentUrl.pathname) {
            if (targetUrl.hash !== '' || link.href.endsWith('#')) {
                return; // Let the browser handle intra-page navigation
            }
        }

        // Prevent the hard reload
        e.preventDefault();
        
        // Execute the soft navigation
        await navigateTo(targetUrl.href);
    });


    // 2. Handle Browser Back/Forward Buttons
    window.addEventListener('popstate', async (e) => {
        // Pass false to prevent pushing a duplicate state to the history stack
        await navigateTo(window.location.href, false);
    });
});



async function navigateTo(url, pushState = true) {
    // Fire event to trigger your UI loader animation
    document.dispatchEvent(new CustomEvent('elara:navigating'));

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const htmlString = await response.text();

        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlString, 'text/html');

        const newTitle = doc.querySelector('title')?.innerText;
        let hasCoreLayout = doc.querySelector('#elara-layout-wrapper');

        if (hasCoreLayout) {
            // --- 1. HEAD & META SYNC ENGINE ---

            // Sync HTML tag attributes (Critical for forced dark-mode themes)
            const newHtmlAttrs = Array.from(doc.documentElement.attributes);
            const currentHtmlAttrs = Array.from(document.documentElement.attributes);

            // 1. Purge stale attributes that exist on the current DOM but NOT on the new page
            currentHtmlAttrs.forEach(attr => {
                if (!doc.documentElement.hasAttribute(attr.name)) {
                    document.documentElement.removeAttribute(attr.name);
                }
            });

            // 2. Add or update attributes from the new page
            newHtmlAttrs.forEach(attr => {
                document.documentElement.setAttribute(attr.name, attr.value);
            });

            // 3. THE SYSTEM RESTORE: Re-apply OS preference if Elara purged the theme
            if (!doc.documentElement.hasAttribute('data-bs-theme')) {
                const storedTheme = localStorage.getItem('theme');
                const preferredTheme = storedTheme ? storedTheme : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', preferredTheme);
            }

            // Diff and Update Stylesheets
            const getBaseHref = (link) => link.href.split('?')[0]; // Ignore ?v= timestamps for diffing
            const newLinks = Array.from(doc.querySelectorAll('link[rel="stylesheet"]'));
            const oldLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));

            // Add new stylesheets
            newLinks.forEach(newLink => {
                if (!oldLinks.some(old => getBaseHref(old) === getBaseHref(newLink))) {
                    document.head.appendChild(newLink.cloneNode(true));
                }
            });

            // Remove obsolete stylesheets
            oldLinks.forEach(oldLink => {
                if (!newLinks.some(newEl => getBaseHref(newEl) === getBaseHref(oldLink))) {
                    oldLink.remove();
                }
            });

            // Update Inline Styles (Head only, to avoid interfering with swapped body styles)
            const newHeadStyles = Array.from(doc.head.querySelectorAll('style'));
            const oldHeadStyles = Array.from(document.head.querySelectorAll('style'));

            oldHeadStyles.forEach(style => style.remove());
            newHeadStyles.forEach(style => document.head.appendChild(style.cloneNode(true)));


            // --- 2. LOADER STATE UPDATE ---
            // Silently update the loader text so it displays correctly on the *next* click
            const newLoaderTitle = doc.querySelector('#page-loader h4');
            const oldLoaderTitle = document.querySelector('#page-loader h4');
            if (newLoaderTitle && oldLoaderTitle) {
                oldLoaderTitle.innerHTML = newLoaderTitle.innerHTML;
            }


            // --- 2A. PRE-SCROLL LOCK ---
            // Assassinate smooth scrolling BEFORE the DOM height changes
            document.documentElement.style.scrollBehavior = 'auto';

            // Snap to top while the old (potentially taller) DOM is still intact
            window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;


            // --- 3. DOM ZONE SWAPPING ---
            const swapZones = [
                'header',                    
                '#elara-layout-wrapper',     
                '#elara-master-footer'   
            ];

            swapZones.forEach(selector => {
                const newEl = doc.querySelector(selector);
                const currentEl = document.querySelector(selector);
                
                if (newEl && currentEl) {
                    currentEl.replaceWith(newEl);

                    // Re-evaluate injected scripts so the audio player fires
                    const newlyInjectedEl = document.querySelector(selector);
                    const scripts = newlyInjectedEl.querySelectorAll('script');
                    
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                }
            });

            // Update title and URL
            if (newTitle) document.title = newTitle;
            if (pushState) window.history.pushState({ url: url }, newTitle, url);

            // --- 4. POST-SCROLL ENFORCEMENT & FOCUS ---
            
            // Force a synchronous layout calculation so the browser knows the exact height of the new DOM
            void document.documentElement.offsetHeight;

            // A 15ms timeout ensures the main thread's render queue has fully cleared
            setTimeout(() => {
                // Enforce the 0,0 scroll axes
                window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
                document.documentElement.scrollTop = 0;
                document.body.scrollTop = 0;

                // THE ANCHOR: Physically move the browser's active focus to the main content area
                const mainContent = document.getElementById('main-content') || document.body;
                mainContent.setAttribute('tabindex', '-1');
                mainContent.focus({ preventScroll: true }); 
                if (mainContent === document.body) mainContent.removeAttribute('tabindex');

                // Resurrect smooth scrolling for the user
                document.documentElement.style.scrollBehavior = '';

                // Dispatch the event
                document.dispatchEvent(new CustomEvent('elara:loaded'));
            }, 15);

        } else {
            window.location.href = url;
        }
    } catch (error) {
        console.error('Elara SPA Error:', error);
        window.location.href = url;
    }
}

// --- ELARA SECURE MAIL OBFUSCATOR ---
function initializeSecureEmails() {
    document.querySelectorAll('.elara-secure-mail').forEach(link => {
        // Prevent double-binding on SPA transitions
        if (link.dataset.secured === "true") return;
        
        const user = link.getAttribute('data-u');
        const domain = link.getAttribute('data-d');
        const tld = link.getAttribute('data-t');
        
        if (user && domain && tld) {
            // Assemble the email in memory
            const email = `${user}@${domain}.${tld}`;
            
            // Set the href for the user
            link.setAttribute('href', `mailto:${email}`);
            
            // Mark as processed so it doesn't run again on this specific link
            link.dataset.secured = "true";
        }
    });
}

// 1. Run on initial hard load
document.addEventListener('DOMContentLoaded', initializeSecureEmails);

// 2. Run every time Elara fetches a new page via soft navigation
document.addEventListener('elara:loaded', initializeSecureEmails);