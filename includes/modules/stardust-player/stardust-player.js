/**
 * ============================================================================
 * THE STARDUST PLAYER ENGINE (v4.4 - SPA Race Condition Fix)
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This file acts as a vanilla JavaScript audio engine tightly integrated with a 
 * Single Page Application (SPA) architecture (specifically the "Elara" framework).
 * It manages media playback, playlist state, user preferences (repeat/shuffle), 
 * accessibility (WCAG), and dynamic content fetching (Markdown lyrics).
 * 
 * CORE RESPONSIBILITIES:
 * 1. Global Event Delegation: Intercepts play/lyrics clicks on dynamically 
 *    injected DOM elements without needing to rebind events upon navigation.
 * 2. Media Playback & State: Controls the HTML5 `<audio>` element, fetching 
 *    blobs, and handling auto-advance/shuffle logic.
 * 3. Accessibility & MediaSession API: Updates OS-level media controls, updates 
 *    page titles, and manages aria labels/live regions for screen readers.
 * 4. Dynamic Lyrics Parser: Fetches external Markdown files, parses basic 
 *    formatting (bold, headers, sections), and renders HTML into a Bootstrap modal.
 * 
 * USAGE:
 * - Requires the existence of specific DOM IDs (found in `sticky-player.php`).
 * - Relies on `window.STARDUST_PLAYLIST` being updated via `stardust:playlist-update` events.
 * 
 * UPDATED FOR ACCESSIBILITY & LORE:
 * 1. Live Regions: Track title now announces "Buffering" and Song Names.
 * 2. Aria-Current: Playlist rows now strictly identify the active track.
 * 3. Page Title: Browser tab updates to reflect playback state.
 * 4. Legacy Tiers: Dynamically injects color-coded lore badges into the UI.
 * 5. WCAG Lore Badges: Fully keyboard focusable.
 * 6. Global Event Delegation: Prevents "Ghost DOM" bugs during Elara SPA navigations.
 * ============================================================================
 */

(function() {
    // --- 1. GLOBAL STATE & CONFIGURATION ---
    let currentIndex = -1;       // Tracks the currently playing song index within the playlist
    let currentBlobUrl = null;   // Holds the memory reference (ObjectURL) to the fetched audio file
    
    // Playback Settings (Persisted in LocalStorage)
    const REPEAT_MODES = ['none', 'all', 'one', 'album'];
    let repeatMode = 'none';
    let isShuffle = false;

    // Load User Preferences from LocalStorage on init
    try {
        const savedRepeat = localStorage.getItem('stardust_repeat_mode');
        if (REPEAT_MODES.includes(savedRepeat)) repeatMode = savedRepeat;
        const savedShuffle = localStorage.getItem('stardust_shuffle_mode');
        if (savedShuffle === 'true') isShuffle = true;
    } catch (e) {
        // Fallback for strict privacy settings (e.g. Safari Private Mode)
        console.warn("LocalStorage access denied.");
    }

    // --- 2. DOM CACHE (Persistent Elements Only) ---
    // These elements exist in footer.php (sticky-player.php) and are NEVER destroyed by Elara.
    // Caching them here improves performance.
    const dom = {
        player: document.getElementById('sticky-audio-player'),
        audio: document.getElementById('main-audio-element'),
        title: document.getElementById('player-track-title'),
        artist: document.getElementById('player-track-artist'),
        art: document.getElementById('player-album-art'),
        btnPrev: document.getElementById('player-prev'),
        btnNext: document.getElementById('player-next'),
        btnRepeat: document.getElementById('player-repeat'),
        btnShuffle: document.getElementById('player-shuffle'),
        btnLyrics: document.getElementById('player-lyrics'),
        btnClose: document.getElementById('btn-close-player'),
        modalElement: document.getElementById('lyricsModal'),
        modalTitle: document.getElementById('lyricsModalTitle'),
        modalContent: document.getElementById('lyricsContent')
    };

    let bsModal = null; // Bootstrap Modal Instance reference

    // ========================================================================
    // INITIALIZATION & EVENT LISTENERS
    // ========================================================================

    /**
     * Bootstraps the audio engine, sets up persistent event listeners,
     * configures accessibility properties, and implements global event delegation.
     */
    function initEngine() {
        if (!dom.player) return; // Safety check - abort if UI is missing

        // WCAG FIX: Make the track title a Live Region so screen readers announce song changes/errors
        dom.title.setAttribute('aria-live', 'polite');
        dom.title.setAttribute('aria-atomic', 'true');

        // Initialize Bootstrap Modal if it exists in the DOM
        if (dom.modalElement) {
            bsModal = new bootstrap.Modal(dom.modalElement);
        }

        // Attach Persistent Controls (Footer Buttons)
        dom.btnPrev.onclick = () => { let idx = getNextIndex(-1); if(idx !== -1) loadTrack(idx); };
        dom.btnNext.onclick = () => { let idx = getNextIndex(1); if(idx !== -1) loadTrack(idx); };
        
        // Cycle through repeat modes and save to LocalStorage
        dom.btnRepeat.onclick = () => {
            const idx = REPEAT_MODES.indexOf(repeatMode);
            repeatMode = REPEAT_MODES[(idx + 1) % REPEAT_MODES.length];
            localStorage.setItem('stardust_repeat_mode', repeatMode);
            updateControlUI();
        };

        // Toggle shuffle mode and save to LocalStorage
        dom.btnShuffle.onclick = () => {
            isShuffle = !isShuffle;
            localStorage.setItem('stardust_shuffle_mode', isShuffle);
            updateControlUI();
        };

        // Trigger lyrics modal
        dom.btnLyrics.onclick = () => openLyrics(dom.btnLyrics.getAttribute('data-title'), dom.btnLyrics.getAttribute('data-url'));
        
        // Close player UI, pause playback, and restore original document title
        dom.btnClose.onclick = () => {
            dom.audio.pause();
            dom.player.classList.add('d-none');
            // Reset page title on close
            document.title = dom.originalPageTitle || document.title;
        };

        // Auto-Advance Logic: Defines behavior when a track finishes naturally
        dom.audio.onended = () => {
            if(repeatMode === 'one') {
                dom.audio.currentTime = 0;
                dom.audio.play();
            } else {
                let idx = getNextIndex(1); 
                if(idx !== -1) loadTrack(idx); 
            }
        };
        
        // Store original title for restoration later
        dom.originalPageTitle = document.title;
        
        updateControlUI();

        // --- THE CURE FOR THE GHOST DOM: GLOBAL EVENT DELEGATION ---
        // This completely replaces bindPageEvents and catches clicks flawlessly.
        // Listens on the document body to catch events on dynamically loaded content.
        document.body.addEventListener('click', (e) => {
            // 1. Play Buttons (Delegated)
            const playBtn = e.target.closest('.btn-play-index');
            if (playBtn) {
                e.preventDefault();
                e.stopPropagation();
                loadTrack(parseInt(playBtn.getAttribute('data-index')));
                return;
            }

            // 2. Lyrics Buttons (Delegated)
            const lyricsBtn = e.target.closest('.btn-view-lyrics');
            if (lyricsBtn) {
                e.preventDefault();
                e.stopPropagation();
                openLyrics(lyricsBtn.getAttribute('data-title'), lyricsBtn.getAttribute('data-url'));
                return;
            }
        });
    }

    // --- TURBO EVENT: PLAYLIST HANDOFF ---
    // Triggered by external scripts (e.g., _tracklist-downloader.php) when a new album/page loads.
    document.addEventListener('stardust:playlist-update', (e) => {
        if(e.detail && e.detail.playlist) {
            window.STARDUST_PLAYLIST = e.detail.playlist;
            
            // If the player is currently hidden (idle), reset the UI to the new album's first track
            // to provide context before the user presses play.
            if (dom.player.classList.contains('d-none') && window.STARDUST_PLAYLIST.length > 0) {
                const first = window.STARDUST_PLAYLIST[0];
                dom.title.innerText = "Ready to Play";
                dom.artist.innerText = first.album; // Show Album Name as Artist initially
                dom.art.src = first.artwork;
            }
            
            // Simply update the active track highlighting
            updateTracklistUI();
        }
    });

    // Run Initialization Once on DOM Load
    document.addEventListener('DOMContentLoaded', () => {
        initEngine();
    });

    // Run on every Elara SPA Navigation to re-sync UI state
    document.addEventListener('elara:loaded', () => {
        updateTracklistUI();
        dom.originalPageTitle = document.title; // Update cached title for the new page
    });


    // ========================================================================
    // CORE LOGIC ENGINE
    // ========================================================================

    /**
     * Determines the index of the next track to play based on current mode.
     * @param {number} direction - 1 for forward, -1 for backward.
     * @returns {number} The next index, or -1 if playback should stop.
     */
    function getNextIndex(direction = 1) {
        const playlist = window.STARDUST_PLAYLIST || [];
        if (playlist.length === 0) return -1;

        if (isShuffle && repeatMode !== 'one') return getRandomIndex();
        if (repeatMode === 'one') return currentIndex;

        if (repeatMode === 'album') {
            const currentTrack = playlist[currentIndex];
            // Simple filter for same album tracks
            const albumIndices = playlist.map((t, i) => (t.album === currentTrack.album ? i : -1)).filter(i => i !== -1);
            let internalPos = albumIndices.indexOf(currentIndex) + direction;
            // Wrap around within the album
            if (internalPos >= albumIndices.length) internalPos = 0;
            if (internalPos < 0) internalPos = albumIndices.length - 1;
            return albumIndices[internalPos];
        }

        let nextIndex = currentIndex + direction;
        if (repeatMode === 'all') {
            // Wrap around the entire playlist
            if (nextIndex >= playlist.length) nextIndex = 0;
            if (nextIndex < 0) nextIndex = playlist.length - 1;
        } else {
            // Stop at edges if no repeat is active
            if (nextIndex >= playlist.length || nextIndex < 0) nextIndex = -1;
        }
        return nextIndex;
    }

    /**
     * Selects a random track index, ensuring it's not the same as the current track (if possible).
     */
    function getRandomIndex() {
        const playlist = window.STARDUST_PLAYLIST || [];
        let newIndex = Math.floor(Math.random() * playlist.length);
        if (newIndex === currentIndex && playlist.length > 1) {
            newIndex = (newIndex + 1) % playlist.length;
        }
        return newIndex;
    }

    // ========================================================================
    // PLAYBACK & UI
    // ========================================================================

    /**
     * Synchronizes the DOM tracklist rows (often generated via PHP/SPA)
     * with the current player state, highlighting the active row.
     */
    function updateTracklistUI() {
        // Reset all rows across the document
        document.querySelectorAll('.track-row').forEach(row => {
            row.classList.remove('bg-primary', 'bg-opacity-25');
            // WCAG FIX: Remove the 'current' marker from inactive rows
            row.removeAttribute('aria-current');
            
            const icon = row.querySelector('.play-indicator');
            if(icon) icon.className = 'fa-duotone fa-play-circle fs-4 text-primary opacity-50 play-indicator';
        });

        // Highlight active row (if it exists on current page view)
        const activeRow = document.getElementById('track-row-' + currentIndex);
        const playlist = window.STARDUST_PLAYLIST || [];
        const currentTrack = playlist[currentIndex];
        
        if(activeRow && currentTrack) {
            // Check if title matches (Safely fallback if no strong tag exists) to ensure we're highlighting the correct row
            const titleElement = activeRow.querySelector('strong');
            const rowTitle = titleElement ? titleElement.innerText : activeRow.innerText;
            
            if (rowTitle.includes(currentTrack.title.trim())) {
                 activeRow.classList.add('bg-primary', 'bg-opacity-25');
                 
                 // WCAG FIX: Mark this row as the 'current' item for screen readers
                 activeRow.setAttribute('aria-current', 'true');

                 const icon = activeRow.querySelector('.play-indicator');
                 if(icon) icon.className = 'spinner-border spinner-border-sm text-light play-indicator';
                 // If playing, change spinner to volume icon
                 if (!dom.audio.paused) {
                    if(icon) icon.className = 'fa-duotone fa-volume-high fs-4 text-white play-indicator';
                 }
            }
        }
    }

    /**
     * Core playback function: Loads the track at `index`, fetches the audio blob,
     * updates the UI, and initializes playback/MediaSession.
     * Attached to window to be accessible from inline onClick handlers or console.
     */
    window.loadTrack = function(index) {
        const playlist = window.STARDUST_PLAYLIST || [];
        if (index < 0 || index >= playlist.length) return;

        // Garbage Collection: Free up memory from previous blob
        if (currentBlobUrl) {
            URL.revokeObjectURL(currentBlobUrl);
            currentBlobUrl = null;
        }

        currentIndex = index;
        const track = playlist[index];

        // UI Updates: Show player and set loading state
        dom.player.classList.remove('d-none');
        
        // NOTE: Because we added aria-live='polite' in initEngine, 
        // this text change will be announced by the screen reader.
        dom.title.innerHTML = `<span class="spinner-border spinner-border-sm text-primary me-2" aria-hidden="true"></span>Buffering...`;
        
        if(dom.artist) dom.artist.textContent = track.artist;
        dom.art.src = track.artwork;
        
        // Update Lyrics Button dataset
        dom.btnLyrics.setAttribute('data-title', track.title);
        dom.btnLyrics.setAttribute('data-url', track.lyrics);

        updateControlUI();
        updateTracklistUI();

        // Fetch & Play: We fetch as a blob to allow caching and avoid certain streaming issues
        fetch(track.src)
            .then(res => {
                if(!res.ok) throw new Error(res.status);
                return res.blob();
            })
            .then(blob => {
                currentBlobUrl = URL.createObjectURL(blob);
                dom.audio.src = currentBlobUrl;
                
                // --- THE LORE ENGINE: Dynamic Legacy Badges (WCAG Compliant) ---
                // Injects specific styling based on the track's metadata 'tier'
                let titleHtml = track.title;
                if (track.legacyTier) {
                    let badgeClass = 'bg-secondary text-white'; // Fallback
                    if (track.legacyTier === 'Chart Smash') badgeClass = 'bg-success text-white';
                    if (track.legacyTier === 'Fan Anthem') badgeClass = 'bg-warning text-dark';
                    if (track.legacyTier === 'Deep Cut') badgeClass = 'bg-info text-dark';
                    if (track.legacyTier === 'Vault Track') badgeClass = 'bg-dark text-warning border border-warning';
                    if (track.legacyTier === 'The Dud' || track.legacyTier === 'Studio Filler') badgeClass = 'bg-danger text-white';

                    // Prepare safe string for HTML attributes
                    let safeLore = track.loreNote ? track.loreNote.replace(/"/g, '&quot;') : '';
                    
                    // WCAG FIX: 
                    // 1. tabindex="0" makes it accessible to keyboard navigation.
                    // 2. aria-label feeds the explicit context to the screen reader.
                    // 3. title attribute provides the visual hover tooltip.
                    // 4. aria-hidden="true" on the inner text prevents the screen reader from reading the tier twice.
                    let wcagAttributes = safeLore 
                        ? `title="${safeLore}" aria-label="Legacy Tier: ${track.legacyTier}. Lore Note: ${safeLore}" tabindex="0"`
                        : `aria-label="Legacy Tier: ${track.legacyTier}" tabindex="0"`;

                    titleHtml += ` <span class="badge ${badgeClass} ms-2 align-text-bottom" style="font-size: 0.6em; letter-spacing: 0.5px; cursor: help;" ${wcagAttributes}><span aria-hidden="true">${track.legacyTier.toUpperCase()}</span></span>`;
                }
                
                dom.title.innerHTML = titleHtml;
                
                // WCAG FIX: Update Browser Title so users know music is playing in this tab
                document.title = `▶ ${track.title} | ${dom.originalPageTitle}`;

                updateTracklistUI(); // Update icon from spinner to Volume

                dom.audio.load();
                dom.audio.play().catch(e => console.warn("Autoplay blocked:", e));
                
                // Media Session API: Updates system-level media controls (e.g., lock screen, control center)
                if ('mediaSession' in navigator) {
                    navigator.mediaSession.metadata = new MediaMetadata({
                        title: track.title, 
                        artist: track.artist, 
                        album: track.album,
                        artwork: [{ src: track.artwork, sizes: '512x512', type: 'image/jpeg' }]
                    });
                    navigator.mediaSession.setActionHandler('previoustrack', () => loadTrack(getNextIndex(-1)));
                    navigator.mediaSession.setActionHandler('nexttrack', () => loadTrack(getNextIndex(1)));
                    navigator.mediaSession.setActionHandler('play', () => dom.audio.play());
                    navigator.mediaSession.setActionHandler('pause', () => dom.audio.pause());
                }
            })
            .catch(err => {
                console.error("Playback Error:", err);
                dom.title.innerHTML = `<span class="text-danger">Load Failed</span>`;
            });
    };

    /**
     * Updates the UI state of player controls (buttons) based on variables.
     */
    function updateControlUI() {
        const rIcon = dom.btnRepeat.querySelector('i');
        dom.btnRepeat.className = 'btn btn-sm ' + (repeatMode === 'none' ? 'btn-outline-secondary' : 'btn-outline-primary active');
        
        // ARIA: Add Pressed state for toggles
        dom.btnRepeat.setAttribute('aria-pressed', repeatMode !== 'none');
        dom.btnShuffle.setAttribute('aria-pressed', isShuffle);

        // Update Repeat Icon depending on mode
        if (repeatMode === 'one') rIcon.className = 'fa-solid fa-repeat-1';
        else if (repeatMode === 'album') rIcon.className = 'fa-duotone fa-compact-disc';
        else rIcon.className = 'fa-solid fa-repeat';
        
        dom.btnShuffle.className = 'btn btn-sm ' + (isShuffle ? 'btn-outline-primary active' : 'btn-outline-secondary');
        
        // Disable Prev/Next buttons conditionally if at boundaries
        dom.btnPrev.disabled = (repeatMode !== 'all' && repeatMode !== 'album' && !isShuffle && currentIndex === 0);
        dom.btnNext.disabled = (repeatMode !== 'all' && repeatMode !== 'album' && !isShuffle && currentIndex === ((window.STARDUST_PLAYLIST || []).length - 1));
    }

    /**
     * FEATURE: openLyrics()
     * Fetches the Markdown (.md) lyrics file and converts it into styled HTML on the fly.
     * This handles custom headers like **LORE NOTE:**
     * Attached to window to allow invocation from dynamically injected views.
     */
    window.openLyrics = function(title, url) {
        if (!bsModal) return; // Guard if modal missing

        // 1. Set Loading State
        dom.modalTitle.textContent = title;
        dom.modalContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 font-monospace">Retrieving data from the Vault...</p>
            </div>`;
        
        bsModal.show();

        // 2. Fetch MD File (with cache busting ?v=timestamp to ensure fresh lore)
        fetch(url + "?v=" + Date.now())
            .then(response => {
                if (!response.ok) throw new Error("Lore file not found.");
                return response.text();
            })
            .then(text => {
                // 3. Parse Markdown
                // Sanitize HTML tags to prevent XSS
                let safeText = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                
                // Split file by double newlines (paragraphs)
                let blocks = safeText.split(/\n\s*\n/);

                // Process each block to apply basic markdown styling
                let htmlOutput = blocks.map(block => {
                    let lines = block.trim().split(/\n/);
                    if (lines.length === 0) return '';

                    let headerHtml = '';
                    let contentLines = lines;
                    let firstLine = lines[0].trim();

                    // Detect "**HEADER:**" pattern (e.g., **LORE NOTE:**)
                    if (firstLine.match(/^\*\*[A-Z ]+:\*\*$/)) {
                        let cleanHeader = firstLine.replace(/\*\*/g, ''); // Strip stars
                        headerHtml = `<h4 class="text-warning fw-bold border-bottom border-secondary pb-2 mb-3 mt-2">${cleanHeader}</h4>`;
                        contentLines = lines.slice(1); // Remove header from body
                    }
                    // Detect "[Section Header]" pattern (e.g., [Chorus])
                    else if (firstLine.match(/^[\(\[].*?[\)\]]$/)) {
                        headerHtml = `<h5 class="text-info fw-bold text-uppercase mb-2 mt-2">${firstLine}</h5>`;
                        contentLines = lines.slice(1);
                    }

                    // Bold specific text within lines
                    // Process text formatting within lines
                    let processedBody = contentLines.map(line => {
                        let parsedLine = line;

                        // 1. Handle Bold (**text** or __text__)
                        // Swap 'text-body' for a color that pops against your dark/light themes
                        parsedLine = parsedLine.replace(/(\*\*|__)(.*?)\1/g, '<strong class="text-warning-emphasis fw-bold">$2</strong>');
                        
                        // 2. Handle Italics (*text* or _text_)
                        // Note: We do this AFTER bold so the double asterisks are already converted!
                        parsedLine = parsedLine.replace(/(\*|_)(.*?)\1/g, '<em class="text-body">$2</em>');

                        // 3. (Optional) Handle Strikethrough (~~text~~)
                        parsedLine = parsedLine.replace(/~~(.*?)~~/g, '<del>$1</del>');

                        return parsedLine;
                    });

                    // Render block output
                    if (contentLines.length === 0) return `<div class="mb-4">${headerHtml}</div>`;

                    return `
                        <div class="lyrics-block mb-4">
                            ${headerHtml}
                            <div style="line-height: 1.6;">${processedBody.join('<br>')}</div>
                        </div>`;
                }).join('');

                // 4. Render Final Content
                dom.modalContent.innerHTML = `<div class="p-3">${htmlOutput}</div>`;
            })
            .catch(err => {
                // Render error state
                dom.modalContent.innerHTML = `<div class="alert alert-warning m-3">Data Corrupted. Unable to retrieve lyrics.</div>`;
            });
    };

})();