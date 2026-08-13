<?php
// --- Component: store-button.php ---
// V2: DSP Streaming + Physical Merch Routing

$type = $storeProps['type'] ?? 'album'; 
$size = $storeProps['size'] ?? 'medium';

// 1. The IDs for each streaming platform
$ids = [
    'spotify' => $storeProps['spotify'] ?? '',
    'apple'   => $storeProps['apple'] ?? '',
    'amazon'  => $storeProps['amazon'] ?? '',
    'youtube' => $storeProps['youtube'] ?? ''
];

// 2. The URLs for Physical Merchandise (Cloudflare Subdomains)
$physical = [
    'vinyl'   => $storeProps['vinyl'] ?? '',
    'cd'      => $storeProps['cd'] ?? '',
    'apparel' => $storeProps['apparel'] ?? ''
];

// Configuration array for streaming platforms
$platforms = [
    'spotify' => ['color' => 'success', 'icon' => 'fa-brands fa-spotify', 'text' => 'Spotify'],
    'apple'   => ['color' => 'danger',  'icon' => 'fa-brands fa-apple',   'text' => 'Apple Music'],
    'amazon'  => ['color' => 'info',    'icon' => 'fa-brands fa-amazon',  'text' => 'Amazon Music'],
    'youtube' => ['color' => 'danger',  'icon' => 'fa-brands fa-youtube', 'text' => 'YouTube']
];

// Configuration array for physical merchandise
$merchConfig = [
    'vinyl'   => ['color' => 'warning', 'icon' => 'fa-solid fa-record-vinyl', 'text' => '12" Vinyl LP'],
    'cd'      => ['color' => 'secondary', 'icon' => 'fa-solid fa-compact-disc', 'text' => 'CD / Box Set'],
    'apparel' => ['color' => 'primary', 'icon' => 'fa-solid fa-shirt', 'text' => 'Apparel & Gear']
];

// Build the specific URLs based on type (Album vs Artist)
$urls = [
    'spotify' => $type === 'artist' ? "https://open.spotify.com/artist/{$ids['spotify']}" : "https://open.spotify.com/album/{$ids['spotify']}",
    'apple'   => $type === 'artist' ? "https://music.apple.com/us/artist/{$ids['apple']}" : "https://music.apple.com/us/album/{$ids['apple']}",
    'amazon'  => $type === 'artist' ? "https://music.amazon.com/artists/{$ids['amazon']}" : "https://music.amazon.com/albums/{$ids['amazon']}",
    'youtube' => $type === 'artist' ? "https://music.youtube.com/channel/{$ids['youtube']}" : "https://music.youtube.com/playlist?list={$ids['youtube']}"
];

// Size formatting
$btnSize = ($size === 'large') ? 'btn-lg' : (($size === 'small') ? 'btn-sm' : '');

// Set the initial default (this will be overwritten by JS if the user has a saved preference)
$default = 'spotify'; 

// Check if we have any physical merch links
$hasMerch = !empty($physical['vinyl']) || !empty($physical['cd']) || !empty($physical['apparel']);
?>

<div class="d-flex flex-wrap gap-2">
    
    <div class="btn-group dynamic-store-group shadow-sm" role="group">
        <a href="<?php echo $urls[$default]; ?>" 
           class="btn btn-<?php echo $platforms[$default]['color']; ?> <?php echo $btnSize; ?> main-store-btn fw-bold px-4"
           data-default-text="<?php echo $type === 'artist' ? 'Artist on' : 'Listen on'; ?>" target="_blank">
            <i class="main-store-icon <?php echo $platforms[$default]['icon']; ?> me-2"></i>
            <span class="main-store-text"><?php echo $type === 'artist' ? 'Artist on ' : 'Listen on '; echo $platforms[$default]['text']; ?></span>
        </a>
        
        <button type="button" class="btn btn-<?php echo $platforms[$default]['color']; ?> <?php echo $btnSize; ?> dropdown-toggle dropdown-toggle-split toggle-store-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Set Default Music App">
            <span class="visually-hidden">Choose Default Music Store</span>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-secondary pb-1">
            <li>
                <div class="px-3 py-2 bg-body-tertiary border-bottom border-secondary-subtle mb-2">
                    <span class="d-block fw-bold text-primary mb-1"><i class="fa-solid fa-memory me-1"></i> Set Global Default</span>
                    <span class="d-block small text-muted lh-sm" style="font-size: 0.8em;">Select your preferred app. We will remember it for all future albums.</span>
                </div>
            </li>
            <?php foreach ($platforms as $key => $data): ?>
                <?php if (!empty($ids[$key])): ?>
                    <li>
                        <a class="dropdown-item store-selector-link py-2" 
                           href="<?php echo $urls[$key]; ?>" 
                           target="_blank"
                           data-platform="<?php echo $key; ?>"
                           data-color="btn-<?php echo $data['color']; ?>"
                           data-icon="<?php echo $data['icon']; ?>"
                           data-name="<?php echo $data['text']; ?>"
                           data-url="<?php echo $urls[$key]; ?>">
                            <i class="<?php echo $data['icon']; ?> me-2 text-<?php echo $data['color']; ?>"></i> <?php echo $data['text']; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($hasMerch): ?>
    <div class="btn-group shadow-sm" role="group">
        <button type="button" class="btn btn-outline-warning <?php echo $btnSize; ?> dropdown-toggle fw-bold px-4" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-cart-shopping me-2"></i>Buy Physical
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-warning pb-1">
            <li>
                <div class="px-3 py-2 bg-body-tertiary border-bottom border-warning-subtle mb-2">
                    <span class="d-block fw-bold text-warning-emphasis mb-1"><i class="fa-solid fa-box-open me-1"></i> Official Merchandise</span>
                    <span class="d-block small text-muted lh-sm" style="font-size: 0.8em;">Orders fulfilled via our on-demand partners.</span>
                </div>
            </li>
            <?php foreach ($physical as $key => $url): ?>
                <?php if (!empty($url)): ?>
                    <li>
                        <a class="dropdown-item py-2 fw-bold" href="<?php echo htmlspecialchars($url); ?>" target="_blank">
                            <i class="<?php echo $merchConfig[$key]['icon']; ?> me-2 text-<?php echo $merchConfig[$key]['color']; ?>"></i> <?php echo $merchConfig[$key]['text']; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>