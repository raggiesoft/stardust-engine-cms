/**
 * ============================================================================
 * THE STARDUST PLAYER ENGINE (v5.0 - Pure Bootstrap 5.3.8 + Offline Synth Fallback)
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This file acts as a vanilla JavaScript audio engine tightly integrated with the
 * Elara Single Page Application (SPA) router. Because `#global-player-zone` lives
 * outside `#elara-layout-wrapper`, audio playback continues without interruption
 * while users navigate across pages.
 * 
 * CORE RESPONSIBILITIES:
 * 1. Global Event Delegation: Intercepts `.btn-play-index` and `.btn-view-lyrics`
 *    clicks on dynamically injected DOM elements without Ghost DOM listener loss.
 * 2. Media Playback & Offline Demo Fallback: Controls the HTML5 `<audio>` element.
 *    If a developer runs the CMS out-of-the-box without external MP3 files on a CDN,
 *    it automatically synthesizes a warm stereo harmonic chord WAV blob in memory so
 *    all playback controls, auto-advance, and MediaSession APIs can be tested live!
 * 3. Accessibility & MediaSession API: Updates OS-level media controls, updates 
 *    page titles, and manages ARIA live regions (`aria-live="polite"`, `aria-current`).
 * 4. Dynamic Lyrics Parser: Fetches external Markdown (`.md`) files, parses basic 
 *    formatting, and renders HTML into the Bootstrap 5 `#lyricsModal`.
 * ============================================================================
 */

(function() {
    // Prevent duplicate initialization if script is evaluated more than once
    if (window.__STARDUST_PLAYER_INITIALIZED) return;
    window.__STARDUST_PLAYER_INITIALIZED = true;

    // --- 1. GLOBAL STATE & CONFIGURATION ---
    let currentIndex = -1;
    let currentBlobUrl = null;
    let currentIsrc = null;
    
    const REPEAT_MODES = ['none', 'all', 'one', 'album'];
    let repeatMode = 'none';
    let isShuffle = false;

    try {
        const savedRepeat = localStorage.getItem('stardust_repeat_mode');
        if (REPEAT_MODES.includes(savedRepeat)) repeatMode = savedRepeat;
        const savedShuffle = localStorage.getItem('stardust_shuffle_mode');
        if (savedShuffle === 'true') isShuffle = true;
    } catch (e) {
        console.warn("LocalStorage access denied.");
    }

    // --- 2. DOM CACHE (Persistent Elements Only) ---
    const dom = {
        player: document.getElementById('sticky-audio-player'),
        audio: document.getElementById('main-audio-element'),
        playIcon: document.getElementById('player-play-icon'),
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

    /**
     * Synthesizes a clean 5-second stereo harmonic WAV blob in memory when running
     * the CMS demo locally without external MP3 files.
     * @param {number} seedIndex - Track index used to vary the chord pitch.
     * @returns {Blob} Valid audio/wav Blob playable by `<audio>`.
     */
    function generateDemoWavBlob(seedIndex) {
        const sampleRate = 22050;
        const durationSeconds = 5;
        const numSamples = sampleRate * durationSeconds;
        const numChannels = 2;
        const bytesPerSample = 2;
        const blockAlign = numChannels * bytesPerSample;
        const byteRate = sampleRate * blockAlign;
        const dataSize = numSamples * blockAlign;
        const buffer = new ArrayBuffer(44 + dataSize);
        const view = new DataView(buffer);

        const writeString = (offset, str) => {
            for (let i = 0; i < str.length; i++) {
                view.setUint8(offset + i, str.charCodeAt(i));
            }
        };

        // RIFF WAV Header
        writeString(0, 'RIFF');
        view.setUint32(4, 36 + dataSize, true);
        writeString(8, 'WAVE');
        writeString(12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true); // PCM
        view.setUint16(22, numChannels, true);
        view.setUint32(24, sampleRate, true);
        view.setUint32(28, byteRate, true);
        view.setUint16(32, blockAlign, true);
        view.setUint16(34, bytesPerSample * 8, true);
        writeString(36, 'data');
        view.setUint32(40, dataSize, true);

        // pleasant synth chords per track index (A minor 9, F major 7, C major 9)
        const chordSets = [
            [220.00, 261.63, 329.63, 392.00, 493.88],
            [174.61, 220.00, 261.63, 329.63, 392.00],
            [261.63, 329.63, 392.00, 493.88, 587.33]
        ];
        const freqs = chordSets[Math.abs(seedIndex) % chordSets.length];

        let offset = 44;
        for (let i = 0; i < numSamples; i++) {
            const t = i / sampleRate;
            // Smooth attack and release envelope
            const attack = Math.min(1, t / 0.4);
            const release = Math.min(1, (durationSeconds - t) / 0.8);
            const env = attack * release * 0.18;

            let sampleL = 0;
            let sampleR = 0;
            for (let f = 0; f < freqs.length; f++) {
                const detune = (f % 2 === 0 ? 1.0015 : 0.9985);
                sampleL += Math.sin(2 * Math.PI * freqs[f] * t);
                sampleR += Math.sin(2 * Math.PI * freqs[f] * detune * t);
            }
            const valL = Math.max(-1, Math.min(1, (sampleL / freqs.length) * env));
            const valR = Math.max(-1, Math.min(1, (sampleR / freqs.length) * env));

            view.setInt16(offset, valL * 32767, true);
            view.setInt16(offset + 2, valR * 32767, true);
            offset += 4;
        }

        return new Blob([buffer], { type: 'audio/wav' });
    }

    /**
     * Displays a lightweight, accessible Bootstrap 5 toast notification.
     */
    function showToast(message) {
        let container = document.getElementById('stardust-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'stardust-toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1080';
            document.body.appendChild(container);
        }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-bg-primary border-0 shadow show';
        toastEl.setAttribute('role', 'status');
        toastEl.setAttribute('aria-live', 'polite');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body small">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>
            </div>
        `;
        container.appendChild(toastEl);

        const closeBtn = toastEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.onclick = () => toastEl.remove();
        setTimeout(() => toastEl.remove(), 4000);
    }

    // ========================================================================
    // INITIALIZATION & EVENT LISTENERS
    // ========================================================================
    function initEngine() {
        if (!dom.player || !dom.audio) return;

        dom.title.setAttribute('aria-live', 'polite');
        dom.title.setAttribute('aria-atomic', 'true');

        // Keep Play/Pause icon synchronized with audio element state
        dom.audio.addEventListener('play', () => {
            if (dom.playIcon) {
                dom.playIcon.classList.remove('fa-play');
                dom.playIcon.classList.add('fa-pause');
            }
            updateTracklistUI();
        });

        dom.audio.addEventListener('pause', () => {
            if (dom.playIcon) {
                dom.playIcon.classList.remove('fa-pause');
                dom.playIcon.classList.add('fa-play');
            }
            updateTracklistUI();
        });

        // Persistent Controls
        dom.btnPrev.onclick = () => { const idx = getNextIndex(-1); if (idx !== -1) window.loadTrack(idx); };
        dom.btnNext.onclick = () => { const idx = getNextIndex(1); if (idx !== -1) window.loadTrack(idx); };
        
        dom.btnRepeat.onclick = () => {
            const idx = REPEAT_MODES.indexOf(repeatMode);
            repeatMode = REPEAT_MODES[(idx + 1) % REPEAT_MODES.length];
            try { localStorage.setItem('stardust_repeat_mode', repeatMode); } catch (e) {}
            updateControlUI();
        };

        dom.btnShuffle.onclick = () => {
            isShuffle = !isShuffle;
            try { localStorage.setItem('stardust_shuffle_mode', isShuffle); } catch (e) {}
            updateControlUI();
        };

        dom.btnLyrics.onclick = () => {
            const title = dom.btnLyrics.getAttribute('data-title') || 'Track Lyrics';
            const url = dom.btnLyrics.getAttribute('data-url');
            if (url) window.openLyrics(title, url);
        };
        
        dom.btnClose.onclick = () => {
            dom.audio.pause();
            dom.player.classList.add('d-none');
            currentIsrc = null;
            document.title = dom.originalPageTitle || document.title;
            updateTracklistUI();
        };

        dom.audio.onended = () => {
            if (repeatMode === 'one') {
                dom.audio.currentTime = 0;
                dom.audio.play();
            } else {
                const idx = getNextIndex(1); 
                if (idx !== -1) window.loadTrack(idx); 
            }
        };
        
        dom.originalPageTitle = document.title;
        updateControlUI();

        // --- GLOBAL EVENT DELEGATION (Prevents Ghost DOM bugs across Elara SPA transitions) ---
        document.body.addEventListener('click', (e) => {
            const playBtn = e.target.closest('.btn-play-index');
            if (playBtn) {
                e.preventDefault();
                e.stopPropagation();
                window.loadTrack(parseInt(playBtn.getAttribute('data-index'), 10));
                return;
            }

            const lyricsBtn = e.target.closest('.btn-view-lyrics');
            if (lyricsBtn) {
                e.preventDefault();
                e.stopPropagation();
                window.openLyrics(lyricsBtn.getAttribute('data-title'), lyricsBtn.getAttribute('data-url'));
                return;
            }
        });
    }

    // --- PLAYLIST HANDOFF EVENT ---
    document.addEventListener('stardust:playlist-update', (e) => {
        if (e.detail && e.detail.playlist) {
            window.STARDUST_PLAYLIST = e.detail.playlist;
            
            if (dom.player && dom.player.classList.contains('d-none') && window.STARDUST_PLAYLIST.length > 0) {
                const first = window.STARDUST_PLAYLIST[0];
                dom.title.innerText = "Ready to Play";
                dom.artist.innerText = first.album || first.artist || "Standby";
                if (first.artwork) dom.art.src = first.artwork;
            }
            
            updateTracklistUI();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEngine);
    } else {
        initEngine();
    }

    document.addEventListener('elara:loaded', () => {
        updateTracklistUI();
        dom.originalPageTitle = document.title;
    });

    // ========================================================================
    // CORE LOGIC ENGINE
    // ========================================================================
    function getNextIndex(direction = 1) {
        const playlist = window.STARDUST_PLAYLIST || [];
        if (playlist.length === 0) return -1;

        if (isShuffle && repeatMode !== 'one') return getRandomIndex();
        if (repeatMode === 'one') return currentIndex;

        if (repeatMode === 'album' && playlist[currentIndex]) {
            const currentTrack = playlist[currentIndex];
            const albumIndices = playlist.map((t, i) => (t.album === currentTrack.album ? i : -1)).filter(i => i !== -1);
            let internalPos = albumIndices.indexOf(currentIndex) + direction;
            if (internalPos >= albumIndices.length) internalPos = 0;
            if (internalPos < 0) internalPos = albumIndices.length - 1;
            return albumIndices[internalPos];
        }

        let nextIndex = currentIndex + direction;
        if (repeatMode === 'all') {
            if (nextIndex >= playlist.length) nextIndex = 0;
            if (nextIndex < 0) nextIndex = playlist.length - 1;
        } else {
            if (nextIndex >= playlist.length || nextIndex < 0) nextIndex = -1;
        }
        return nextIndex;
    }

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
    function updateTracklistUI() {
        document.querySelectorAll('.track-row').forEach(row => {
            row.style.backgroundColor = '';
            row.removeAttribute('aria-current');
            
            const icon = row.querySelector('.play-indicator');
            if (icon) {
                icon.className = 'play-indicator fa-solid fa-circle-play fs-3 text-secondary opacity-50';
            }
        });

        const activeRow = document.getElementById('track-row-' + currentIndex);
        const playlist = window.STARDUST_PLAYLIST || [];
        const currentTrack = playlist[currentIndex];
        
        if (activeRow && currentTrack && dom.player && !dom.player.classList.contains('d-none')) {
            const titleElement = activeRow.querySelector('strong');
            const rowTitle = titleElement ? titleElement.innerText : activeRow.innerText;
            
            if (rowTitle.includes(currentTrack.title.trim())) {
                activeRow.style.backgroundColor = 'rgba(var(--bs-primary-rgb), 0.12)';
                activeRow.setAttribute('aria-current', 'true');

                const icon = activeRow.querySelector('.play-indicator');
                if (icon) {
                    icon.className = dom.audio.paused
                        ? 'play-indicator fa-solid fa-circle-pause fs-3 text-primary'
                        : 'play-indicator fa-solid fa-volume-high fs-3 text-primary';
                }
            }
        }
    }

    window.loadTrack = function(index) {
        const playlist = window.STARDUST_PLAYLIST || [];
        if (index < 0 || index >= playlist.length || !dom.player) return;

        const track = playlist[index];

        // --- ISRC DEDUPLICATION CHECK ---
        if (track.isrc && currentIsrc && track.isrc === currentIsrc && !dom.player.classList.contains('d-none')) {
            currentIndex = index;
            updateTracklistUI();
            
            if (dom.audio.paused) {
                dom.audio.play();
            } else {
                showToast("<strong>Continuous Playback:</strong> You are already listening to this track.");
            }
            return;
        }

        if (currentBlobUrl) {
            URL.revokeObjectURL(currentBlobUrl);
            currentBlobUrl = null;
        }

        currentIndex = index;
        currentIsrc = track.isrc || null;

        // Unhide player (remove Bootstrap's `d-none` utility class)
        dom.player.classList.remove('d-none');
        
        dom.title.innerHTML = `<span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>Buffering...`;
        if (dom.artist) dom.artist.textContent = track.artist || track.album || '';
        if (track.artwork) dom.art.src = track.artwork;
        
        dom.btnLyrics.setAttribute('data-title', track.title || 'Lyrics');
        dom.btnLyrics.setAttribute('data-url', track.lyrics || '');
        dom.btnLyrics.style.display = track.lyrics ? '' : 'none';

        updateControlUI();
        updateTracklistUI();

        const playResolvedBlob = (blob, isSynthFallback = false) => {
            currentBlobUrl = URL.createObjectURL(blob);
            dom.audio.src = currentBlobUrl;
            
            let titleHtml = track.title;
            if (track.legacyTier) {
                let badgeClass = 'bg-secondary-subtle text-secondary-emphasis';
                if (track.legacyTier === 'Chart Smash') badgeClass = 'bg-success-subtle text-success-emphasis';
                if (track.legacyTier === 'Fan Anthem') badgeClass = 'bg-warning-subtle text-warning-emphasis';
                if (track.legacyTier === 'Deep Cut') badgeClass = 'bg-info-subtle text-info-emphasis';
                if (track.legacyTier === 'The Dud' || track.legacyTier === 'Studio Filler') badgeClass = 'bg-danger-subtle text-danger-emphasis';

                const safeLore = track.loreNote ? track.loreNote.replace(/"/g, '&quot;') : '';
                const wcagAttributes = safeLore 
                    ? `title="${safeLore}" aria-label="Tier: ${track.legacyTier}. Note: ${safeLore}" tabindex="0"`
                    : `aria-label="Tier: ${track.legacyTier}" tabindex="0"`;

                titleHtml += ` <span class="badge ${badgeClass} ms-1 align-text-bottom" style="font-size: 0.65em; cursor: help;" ${wcagAttributes}><span aria-hidden="true">${track.legacyTier.toUpperCase()}</span></span>`;
            }
            if (isSynthFallback) {
                titleHtml += ` <span class="badge bg-primary-subtle text-primary-emphasis ms-1 align-text-bottom" style="font-size: 0.65em;" title="Synthesized Web Audio Demo Tone">DEMO SYNTH</span>`;
            }
            
            dom.title.innerHTML = titleHtml;
            document.title = `▶ ${track.title} | ${dom.originalPageTitle}`;

            dom.audio.load();
            dom.audio.play().catch(e => console.warn("Autoplay blocked:", e));
            updateTracklistUI();
            
            if ('mediaSession' in navigator) {
                navigator.mediaSession.metadata = new MediaMetadata({
                    title: track.title, 
                    artist: track.artist || '', 
                    album: track.album || '',
                    artwork: track.artwork ? [{ src: track.artwork, sizes: '512x512' }] : []
                });
                navigator.mediaSession.setActionHandler('previoustrack', () => window.loadTrack(getNextIndex(-1)));
                navigator.mediaSession.setActionHandler('nexttrack', () => window.loadTrack(getNextIndex(1)));
                navigator.mediaSession.setActionHandler('play', () => dom.audio.play());
                navigator.mediaSession.setActionHandler('pause', () => dom.audio.pause());
            }
        };

        // If no external MP3 exists (or demo mode), fallback gracefully to synthesized stereo WAV blob
        if (!track.src || track.src.startsWith('demo:')) {
            playResolvedBlob(generateDemoWavBlob(index), true);
            return;
        }

        fetch(track.src)
            .then(res => {
                if (!res.ok) throw new Error(res.status);
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('text/html')) throw new Error('HTML fallback returned instead of audio');
                return res.blob();
            })
            .then(blob => playResolvedBlob(blob, false))
            .catch(() => {
                // Graceful local developer fallback: synthesize audio blob so demo always plays!
                playResolvedBlob(generateDemoWavBlob(index), true);
            });
    };

    function updateControlUI() {
        if (!dom.btnRepeat || !dom.btnShuffle) return;
        const rIcon = dom.btnRepeat.querySelector('i');
        dom.btnRepeat.style.opacity = (repeatMode === 'none' ? '0.5' : '1');
        dom.btnRepeat.style.color = (repeatMode === 'none' ? '' : 'var(--bs-primary)');
        
        dom.btnRepeat.setAttribute('aria-pressed', String(repeatMode !== 'none'));
        dom.btnShuffle.setAttribute('aria-pressed', String(isShuffle));

        if (rIcon) {
            if (repeatMode === 'one') rIcon.className = 'fa-solid fa-1';
            else if (repeatMode === 'album') rIcon.className = 'fa-solid fa-compact-disc';
            else rIcon.className = 'fa-solid fa-repeat';
        }
        
        dom.btnShuffle.style.opacity = (isShuffle ? '1' : '0.5');
        dom.btnShuffle.style.color = (isShuffle ? 'var(--bs-primary)' : '');
        
        const playlistLen = (window.STARDUST_PLAYLIST || []).length;
        dom.btnPrev.disabled = (repeatMode !== 'all' && repeatMode !== 'album' && !isShuffle && currentIndex <= 0);
        dom.btnNext.disabled = (repeatMode !== 'all' && repeatMode !== 'album' && !isShuffle && currentIndex >= (playlistLen - 1));
    }

    /**
     * Fetches the Markdown (.md) lyrics file and renders it inside the Bootstrap 5 `#lyricsModal`.
     */
    window.openLyrics = function(title, url) {
        if (!dom.modalElement) return;

        dom.modalTitle.innerHTML = `<i class="fa-solid fa-file-lines me-2 text-primary" aria-hidden="true"></i>${title || 'Lyrics'}`;
        dom.modalContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <p class="mt-3 font-monospace small text-body-secondary mb-0">Loading Markdown lyrics...</p>
            </div>`;
        
        // Show Bootstrap 5 Modal
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(dom.modalElement).show();
        } else {
            dom.modalElement.classList.add('show');
            dom.modalElement.style.display = 'block';
        }

        if (!url) {
            dom.modalContent.innerHTML = `<div class="alert alert-secondary m-3">No lyrics file configured for this track.</div>`;
            return;
        }

        const sep = url.includes('?') ? '&' : '?';
        fetch(url + sep + "v=" + Date.now())
            .then(response => {
                if (!response.ok) throw new Error("Lyrics file not found.");
                return response.text();
            })
            .then(text => {
                let safeText = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                let blocks = safeText.split(/\n\s*\n/);

                let htmlOutput = blocks.map(block => {
                    let lines = block.trim().split(/\n/);
                    if (lines.length === 0) return '';

                    let headerHtml = '';
                    let contentLines = lines;
                    let firstLine = lines[0].trim();

                    if (firstLine.match(/^\*\*[A-Z0-9 _-]+:\*\*$/)) {
                        let cleanHeader = firstLine.replace(/\*\*/g, '');
                        headerHtml = `<h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">${cleanHeader}</h3>`;
                        contentLines = lines.slice(1);
                    } else if (firstLine.match(/^[\(\[].*?[\)\]]$/)) {
                        headerHtml = `<h4 class="h6 fw-bold text-uppercase text-info mt-3 mb-2">${firstLine}</h4>`;
                        contentLines = lines.slice(1);
                    }

                    let processedBody = contentLines.map(line => {
                        let parsedLine = line;
                        parsedLine = parsedLine.replace(/(\*\*|__)(.*?)\1/g, '<strong class="text-body-emphasis fw-bold">$2</strong>');
                        parsedLine = parsedLine.replace(/(\*|_)(.*?)\1/g, '<em class="fst-italic">$2</em>');
                        parsedLine = parsedLine.replace(/`(.*?)`/g, '<code class="px-1 rounded bg-body-tertiary">$1</code>');
                        return parsedLine;
                    });

                    if (contentLines.length === 0) return `<div class="mb-3">${headerHtml}</div>`;

                    return `
                        <div class="lyrics-block mb-4">
                            ${headerHtml}
                            <div class="lh-lg">${processedBody.join('<br>')}</div>
                        </div>`;
                }).join('');

                dom.modalContent.innerHTML = `<div class="p-2">${htmlOutput}</div>`;
            })
            .catch(() => {
                dom.modalContent.innerHTML = `<div class="alert alert-warning m-3">Unable to retrieve Markdown lyrics file from <code>${url}</code>.</div>`;
            });
    };

})();