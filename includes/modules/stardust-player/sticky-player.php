<?php
/**
 * ============================================================================
 * STARDUST ENGINE - STICKY PLAYER MODULE
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This file serves as the DOM UI shell for the Stardust Player Module. It is 
 * designed to be a persistent, floating UI component (included in `footer.php`
 * outside `#elara-layout-wrapper` and `#elara-master-footer`) that remains
 * active and uninterrupted across Elara SPA navigations.
 * 
 * KEY FEATURES:
 * - Responsive, glassmorphism-styled floating audio player using Bootstrap 5.3.8.
 * - Accessibility-focused structural design (`aria-live`, `aria-label`, `aria-pressed`).
 * - Contains the root HTML5 `<audio>` element (`#main-audio-element`).
 * - Provides the UI layout for track info, album art, playback controls, and Bootstrap 5 lyrics modal.
 * ============================================================================
 */
?>

<!-- Main Player Container: Fixed, floating, hidden by default (`d-none`), glassmorphism styling -->
<div id="sticky-audio-player" role="region" aria-label="Persistent Audio Player" class="card shadow-lg border-primary border-opacity-50 d-none m-2 m-md-4 rounded-4 overflow-hidden" style="max-width: 600px; pointer-events: auto; background: rgba(var(--bs-body-bg-rgb), 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
    <div class="row g-0 align-items-center">
        
        <!-- Album Art Section -->
        <div class="col-3 col-sm-2 position-relative">
            <img id="player-album-art" 
                 src="/assets/json/sample-album/album-art.svg" 
                 class="img-fluid h-100 object-fit-cover" 
                 alt="Current Track Album Art" 
                 style="min-height: 80px;">
        </div>

        <!-- Player Controls & Info Section -->
        <div class="col-7 col-sm-8 px-3 py-2">
            
            <!-- Track Info (Title & Artist) -->
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="text-truncate w-100 me-2" aria-live="polite">
                    <strong id="player-track-title" class="d-block text-truncate small mb-0 text-body-emphasis">Ready to Play</strong>
                    <span id="player-track-artist" class="small text-body-secondary text-truncate d-block" style="font-size: 0.75rem;">Standby</span>
                </div>
            </div>

            <!-- The Core HTML5 Audio Element -->
            <audio id="main-audio-element" preload="auto"></audio>

            <!-- Playback Controls -->
            <div class="d-flex align-items-center justify-content-between w-100 mt-2">
                
                <!-- Primary Controls: Prev, Play/Pause, Next -->
                <div class="d-flex align-items-center gap-1">
                    <button id="player-prev" type="button" class="btn btn-sm btn-link text-body-secondary p-1" aria-label="Previous Track">
                        <i class="fa-solid fa-backward-step" aria-hidden="true"></i>
                    </button>
                    <button id="player-play-toggle" type="button" class="btn btn-sm btn-primary rounded-circle shadow-sm" onclick="document.getElementById('main-audio-element').paused ? document.getElementById('main-audio-element').play() : document.getElementById('main-audio-element').pause()" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" aria-label="Play or Pause">
                        <i class="fa-solid fa-play fa-sm" id="player-play-icon" aria-hidden="true"></i>
                    </button>
                    <button id="player-next" type="button" class="btn btn-sm btn-link text-body-secondary p-1" aria-label="Next Track">
                        <i class="fa-solid fa-forward-step" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Secondary Controls: Repeat, Shuffle, Lyrics -->
                <div class="d-flex align-items-center gap-1">
                    <button id="player-repeat" type="button" class="btn btn-sm btn-outline-secondary p-1 border-0" aria-label="Toggle Repeat Mode" title="Toggle Repeat Mode">
                        <i class="fa-solid fa-repeat" aria-hidden="true"></i>
                    </button>
                    <button id="player-shuffle" type="button" class="btn btn-sm btn-outline-secondary p-1 border-0" aria-label="Toggle Shuffle Mode" title="Toggle Shuffle Mode">
                        <i class="fa-solid fa-shuffle" aria-hidden="true"></i>
                    </button>
                    <button id="player-lyrics" type="button" class="btn btn-sm btn-outline-info p-1 border-0 ms-1" data-title="" data-url="" aria-label="View Track Lyrics" title="View Track Lyrics">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

        </div>

        <!-- Close Button Section -->
        <div class="col-2 col-sm-2 text-end pe-3">
            <button id="btn-close-player" type="button" class="btn-close" aria-label="Close Audio Player"></button>
        </div>
    </div>
</div>

<!-- Lyrics Modal: Standard Bootstrap 5.3.8 Modal used to display dynamically fetched Markdown lyrics -->
<div class="modal fade" id="lyricsModal" tabindex="-1" aria-labelledby="lyricsModalTitle" aria-hidden="true" style="pointer-events: auto;" <?php echo !empty($isDarkTheme) ? 'data-bs-theme="dark"' : ''; ?>>
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content shadow">
      <!-- Modal Header -->
      <div class="modal-header border-bottom bg-body-tertiary">
        <h2 class="modal-title h5 font-monospace fw-bold text-uppercase mb-0" id="lyricsModalTitle">
            <i class="fa-solid fa-file-lines me-2 text-primary" aria-hidden="true"></i>Track Lyrics
        </h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close Lyrics Modal"></button>
      </div>
      <!-- Modal Body (Content injected by stardust-player.js) -->
      <div class="modal-body p-0">
        <div id="lyricsContent" class="p-4 bg-body font-monospace" style="font-size: 1.05rem; line-height: 1.75;">
        </div>
      </div>
    </div>
  </div>
</div>