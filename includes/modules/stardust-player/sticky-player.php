<?php
// includes/modules/stardust-player/sticky-player.php
// The DOM UI for the Stardust Player Module
?>

<div id="sticky-audio-player" class="card shadow-lg border-primary border-opacity-50 d-none m-2 m-md-4 rounded-4 overflow-hidden" style="max-width: 600px; background: rgba(var(--bs-body-bg-rgb), 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
    <div class="row g-0 align-items-center">
        <div class="col-3 col-sm-2 position-relative">
            <img id="player-album-art" 
                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='currentColor' class='bi bi-vinyl-fill' viewBox='0 0 16 16'%3E%3Cpath d='M8 6a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm0 3a1 1 0 1 1 0-2 1 1 0 0 1 0 2z'/%3E%3Cpath d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4 8a4 4 0 1 0 8 0 4 4 0 0 0-8 0z'/%3E%3C/svg%3E" 
                 class="img-fluid h-100 object-fit-cover" 
                 alt="Album Art" 
                 style="min-height: 80px;">
        </div>

        <div class="col-7 col-sm-8 px-3 py-2">
            
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="text-truncate w-100 me-2" aria-live="polite">
                    <strong id="player-track-title" class="d-block text-truncate small mb-0 text-body-emphasis">Loading...</strong>
                    <span id="player-track-artist" class="small text-body-secondary text-truncate d-block" style="font-size: 0.75rem;">Standby</span>
                </div>
            </div>

            <audio id="main-audio-element" preload="auto"></audio>

            <div class="d-flex align-items-center justify-content-between w-100 mt-2">
                <div class="d-flex align-items-center gap-1">
                    <button id="player-prev" class="btn btn-sm btn-link text-body-secondary p-1" aria-label="Previous Track">
                        <i class="fa-solid fa-backward-step"></i>
                    </button>
                    <button class="btn btn-sm btn-primary rounded-circle shadow-sm" onclick="document.getElementById('main-audio-element').paused ? document.getElementById('main-audio-element').play() : document.getElementById('main-audio-element').pause()" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" aria-label="Play/Pause">
                        <i class="fa-solid fa-play fa-sm" id="player-play-icon"></i>
                    </button>
                    <button id="player-next" class="btn btn-sm btn-link text-body-secondary p-1" aria-label="Next Track">
                        <i class="fa-solid fa-forward-step"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center gap-1">
                    <button id="player-repeat" class="btn btn-sm btn-outline-secondary p-1 border-0" aria-label="Toggle Repeat">
                        <i class="fa-solid fa-repeat"></i>
                    </button>
                    <button id="player-shuffle" class="btn btn-sm btn-outline-secondary p-1 border-0" aria-label="Toggle Shuffle">
                        <i class="fa-solid fa-shuffle"></i>
                    </button>
                    <button id="player-lyrics" class="btn btn-sm btn-outline-info p-1 border-0 ms-1" data-title="" data-url="" aria-label="View Lyrics">
                        <i class="fa-solid fa-file-lines"></i>
                    </button>
                </div>
            </div>

        </div>

        <div class="col-2 col-sm-2 text-end pe-3">
            <button id="btn-close-player" class="btn-close" aria-label="Close Player"></button>
        </div>
    </div>
</div>

<script>
// Simple toggle listener for the play icon
const audioEl = document.getElementById('main-audio-element');
const playIcon = document.getElementById('player-play-icon');

if(audioEl && playIcon) {
    audioEl.addEventListener('play', () => {
        playIcon.classList.remove('fa-play');
        playIcon.classList.add('fa-pause');
    });
    audioEl.addEventListener('pause', () => {
        playIcon.classList.remove('fa-pause');
        playIcon.classList.add('fa-play');
    });
}
</script>

<div class="modal fade" id="lyricsModal" tabindex="-1" aria-labelledby="lyricsModalTitle" aria-hidden="true" <?php echo $isDarkTheme ? 'data-bs-theme="dark"' : ''; ?>>
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content shadow">
      <div class="modal-header border-bottom-0 bg-body-tertiary">
        <h5 class="modal-title font-monospace fw-bold text-uppercase" id="lyricsModalTitle">
            <i class="fa-solid fa-file-lines me-2 text-info"></i>Loading...
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="lyricsContent" class="p-4 bg-body font-monospace" style="font-size: 1.1rem; line-height: 1.8;">
            </div>
      </div>
    </div>
  </div>
</div>