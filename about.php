<?php
require_once "config/db.php";
require_once "includes/helpers.php";

$settings = getSiteSettings($conn);
$siteName = $settings['site_name'] ?? 'SOUND Entertainment';
$description = $settings['site_description'] ?? 'Your destination for music and videos.';

$pageTitle = "About - " . $siteName;
$basePath = "";
include "includes/header.php";
?>

<div class="hero-banner">
    <h1 class="mb-3">About <?= e($siteName) ?></h1>
    <p class="mb-4"><?= e($description) ?></p>
    <div class="d-flex flex-wrap gap-2">
        <a href="music.php" class="btn btn-primary px-4">Explore Music</a>
        <a href="videos.php" class="btn btn-outline-light px-4">Watch Videos</a>
        <a href="contact.php" class="btn btn-outline-accent px-4">Contact Us</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h4 class="mb-2">Who We Are</h4>
            <p class="text-muted mb-0">
                SOUND Entertainment brings songs, music videos, artists, and albums together in a straightforward platform built for everyday listeners.
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h4 class="mb-2">Our Goal</h4>
            <p class="text-muted mb-0">
                Make it easy to browse music and videos by artist, album, release year, genre, and language, and let listeners share honest ratings and reviews.
            </p>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-4">
            <h4 class="mb-3">Platform Features</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="music.php" class="review-box d-block h-100">
                        <h6 class="text-white mb-1">Music &amp; Video Streaming</h6>
                        <p class="text-muted small mb-0">Play MP3 tracks and watch videos right in your browser.</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="search.php" class="review-box d-block h-100">
                        <h6 class="text-white mb-1">Filtered Search</h6>
                        <p class="text-muted small mb-0">Search across artists, albums, genres, languages, and years.</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="register.php" class="review-box d-block h-100">
                        <h6 class="text-white mb-1">Ratings &amp; Reviews</h6>
                        <p class="text-muted small mb-0">Sign up to rate releases and post your own reviews.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
