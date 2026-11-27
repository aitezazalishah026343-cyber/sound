<?php
require_once "config/db.php";
require_once "includes/helpers.php";

$settings = getSiteSettings($conn);
$siteName = $settings['site_name'] ?? 'SOUND Entertainment';
$siteDescription = $settings['site_description'] ?? 'Listen to new releases, watch music videos, and explore artists and albums.';

$pageTitle = $siteName;
$basePath = "";
include "includes/header.php";

if (isset($_GET['registered'])) {
    echo '<div class="alert alert-success alert-dismissible fade show">Welcome! Your account is ready.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

$music = $conn->query(
    "SELECT m.*, a.artist_name
     FROM music m
     LEFT JOIN artists a ON m.artist_id = a.id
     ORDER BY m.created_at DESC, m.id DESC
     LIMIT 8"
);

$videos = $conn->query(
    "SELECT v.*, a.artist_name
     FROM videos v
     LEFT JOIN artists a ON v.artist_id = a.id
     ORDER BY v.created_at DESC, v.id DESC
     LIMIT 8"
);

$artists = $conn->query(
    "SELECT a.*,
            (SELECT COUNT(*) FROM music m WHERE m.artist_id = a.id) AS total_songs
     FROM artists a
     ORDER BY a.id DESC
     LIMIT 4"
);
?>

<section class="hero-banner">
    <h1 class="mb-3">Music &amp; Videos,<br>All in One Place.</h1>
    <p class="mb-4"><?= e($siteDescription) ?></p>

    <form action="search.php" method="GET" class="row g-2 mb-4" style="max-width: 520px;">
        <div class="col-sm-8">
            <input type="text" name="q" class="form-control" placeholder="Search tracks, videos, artists...">
        </div>
        <div class="col-sm-4">
            <button type="submit" class="btn btn-primary w-100 h-100">Search</button>
        </div>
    </form>

    <div class="d-flex flex-wrap gap-2">
        <a href="music.php" class="btn btn-primary px-4">Browse Music</a>
        <a href="videos.php" class="btn btn-outline-light px-4">Watch Videos</a>
        <a href="artists.php" class="btn btn-outline-accent px-4">Artists</a>
        <a href="albums.php" class="btn btn-outline-light px-4">Albums</a>
    </div>
</section>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="section-heading">Latest Music</h2>
    <a href="music.php" class="link-accent small fw-semibold">View all &rarr;</a>
</div>

<div class="row g-4 mb-5">
    <?php if ($music && $music->num_rows > 0): ?>
        <?php while ($row = $music->fetch_assoc()): ?>
            <?php $cover = !empty($row['cover_image']) ? $row['cover_image'] : 'assets/images/music-placeholder.svg'; ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="music_details.php?id=<?= (int)$row['id'] ?>">
                        <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($row['title']) ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <h5 class="card-title mb-0">
                                <a href="music_details.php?id=<?= (int)$row['id'] ?>"><?= e($row['title']) ?></a>
                            </h5>
                            <?php if (!empty($row['is_new'])): ?>
                                <span class="tag-new">NEW</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3">
                            <?php if (!empty($row['artist_id']) && !empty($row['artist_name'])): ?>
                                <a href="artist_details.php?id=<?= (int)$row['artist_id'] ?>" class="link-accent">
                                    <?= e($row['artist_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($row['artist_name'] ?? 'Unknown Artist') ?>
                            <?php endif; ?>
                            <?php if (!empty($row['release_year'])): ?>
                                &middot; <?= e($row['release_year']) ?>
                            <?php endif; ?>
                        </p>

                        <?php if (!empty($row['music_file'])): ?>
                            <audio controls preload="none" class="w-100 mt-auto">
                                <source src="<?= e($row['music_file']) ?>">
                            </audio>
                        <?php endif; ?>

                        <a href="music_details.php?id=<?= (int)$row['id'] ?>" class="btn btn-primary btn-sm mt-3">
                            Listen &amp; Review
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-4 text-center text-muted">No music tracks uploaded yet.</div>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="section-heading">Latest Videos</h2>
    <a href="videos.php" class="link-accent small fw-semibold">View all &rarr;</a>
</div>

<div class="row g-4 mb-5">
    <?php if ($videos && $videos->num_rows > 0): ?>
        <?php while ($row = $videos->fetch_assoc()): ?>
            <?php $thumb = !empty($row['thumbnail']) ? $row['thumbnail'] : 'assets/images/video-placeholder.svg'; ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="video_details.php?id=<?= (int)$row['id'] ?>">
                        <img src="<?= e($thumb) ?>" class="card-img-top" alt="<?= e($row['title']) ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <h5 class="card-title mb-0">
                                <a href="video_details.php?id=<?= (int)$row['id'] ?>"><?= e($row['title']) ?></a>
                            </h5>
                            <?php if (!empty($row['is_new'])): ?>
                                <span class="tag-new">NEW</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3">
                            <?php if (!empty($row['artist_id']) && !empty($row['artist_name'])): ?>
                                <a href="artist_details.php?id=<?= (int)$row['artist_id'] ?>" class="link-accent">
                                    <?= e($row['artist_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($row['artist_name'] ?? 'Unknown Artist') ?>
                            <?php endif; ?>
                            <?php if (!empty($row['release_year'])): ?>
                                &middot; <?= e($row['release_year']) ?>
                            <?php endif; ?>
                        </p>

                        <a href="video_details.php?id=<?= (int)$row['id'] ?>" class="btn btn-primary btn-sm mt-auto">
                            Watch Video
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-4 text-center text-muted">No videos uploaded yet.</div>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="section-heading">Artists</h2>
    <a href="artists.php" class="link-accent small fw-semibold">All artists &rarr;</a>
</div>

<div class="row g-4">
    <?php if ($artists && $artists->num_rows > 0): ?>
        <?php while ($artist = $artists->fetch_assoc()): ?>
            <?php $img = !empty($artist['image']) ? $artist['image'] : 'assets/images/artist-placeholder.svg'; ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="artist_details.php?id=<?= (int)$artist['id'] ?>">
                        <img src="<?= e($img) ?>" class="card-img-top" alt="<?= e($artist['artist_name']) ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1"><?= e($artist['artist_name']) ?></h5>
                        <p class="text-muted small mb-3"><?= (int)$artist['total_songs'] ?> tracks</p>
                        <a href="artist_details.php?id=<?= (int)$artist['id'] ?>" class="btn btn-outline-light btn-sm mt-auto">
                            View Artist
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-4 text-center text-muted">No artists added yet.</div>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
