<?php
require_once "config/db.php";

$pageTitle = "Videos - SOUND Entertainment";
$basePath = "";
include "includes/header.php";

$sql = "SELECT v.*, a.artist_name, g.genre_name
        FROM videos v
        LEFT JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        ORDER BY v.created_at DESC, v.id DESC";

$result = $conn->query($sql);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1">Video Library</h1>
        <p class="text-muted mb-0">Watch music videos and rate your favourites.</p>
    </div>
    <a href="search.php?type=video" class="btn btn-outline-accent">Filter Videos</a>
</div>

<div class="row g-4">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($video = $result->fetch_assoc()): ?>
            <?php $thumb = !empty($video['thumbnail']) ? $video['thumbnail'] : 'assets/images/video-placeholder.svg'; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="video_details.php?id=<?= (int)$video['id'] ?>">
                        <img src="<?= e($thumb) ?>" class="card-img-top" alt="<?= e($video['title']) ?>">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="card-title mb-0">
                                <a href="video_details.php?id=<?= (int)$video['id'] ?>"><?= e($video['title']) ?></a>
                            </h5>
                            <?php if (!empty($video['is_new'])): ?>
                                <span class="tag-new">NEW</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3">
                            Artist:
                            <?php if (!empty($video['artist_id']) && !empty($video['artist_name'])): ?>
                                <a href="artist_details.php?id=<?= (int)$video['artist_id'] ?>" class="link-accent">
                                    <?= e($video['artist_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($video['artist_name'] ?? 'Unknown') ?>
                            <?php endif; ?>
                            <br>
                            Year: <?= e($video['release_year'] ?: 'N/A') ?>
                            <?php if (!empty($video['genre_name'])): ?>
                                &middot; <?= e($video['genre_name']) ?>
                            <?php endif; ?>
                        </p>

                        <?php if (!empty($video['video_file'])): ?>
                            <video controls preload="none" class="w-100 mt-auto">
                                <source src="<?= e($video['video_file']) ?>">
                            </video>
                        <?php endif; ?>

                        <a href="video_details.php?id=<?= (int)$video['id'] ?>" class="btn btn-primary w-100 mt-3">
                            Details &amp; Reviews
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted">No videos available yet.</div>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
