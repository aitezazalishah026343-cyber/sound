<?php
require_once "config/db.php";

$pageTitle = "Music - SOUND Entertainment";
$basePath = "";
include "includes/header.php";

$sql = "SELECT m.*, a.artist_name, al.album_name, g.genre_name
        FROM music m
        LEFT JOIN artists a ON m.artist_id = a.id
        LEFT JOIN albums al ON m.album_id = al.id
        LEFT JOIN genres g ON m.genre_id = g.id
        ORDER BY m.created_at DESC, m.id DESC";

$result = $conn->query($sql);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1">Music Library</h1>
        <p class="text-muted mb-0">Stream tracks and read listener reviews.</p>
    </div>
    <a href="search.php?type=music" class="btn btn-outline-accent">Filter Music</a>
</div>

<div class="row g-4">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($song = $result->fetch_assoc()): ?>
            <?php $cover = !empty($song['cover_image']) ? $song['cover_image'] : 'assets/images/music-placeholder.svg'; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="music_details.php?id=<?= (int)$song['id'] ?>">
                        <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($song['title']) ?>">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="card-title mb-0">
                                <a href="music_details.php?id=<?= (int)$song['id'] ?>"><?= e($song['title']) ?></a>
                            </h5>
                            <?php if (!empty($song['is_new'])): ?>
                                <span class="tag-new">NEW</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3">
                            Artist:
                            <?php if (!empty($song['artist_id']) && !empty($song['artist_name'])): ?>
                                <a href="artist_details.php?id=<?= (int)$song['artist_id'] ?>" class="link-accent">
                                    <?= e($song['artist_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($song['artist_name'] ?? 'Unknown') ?>
                            <?php endif; ?>
                            <br>
                            Album:
                            <?php if (!empty($song['album_id']) && !empty($song['album_name'])): ?>
                                <a href="album_details.php?id=<?= (int)$song['album_id'] ?>" class="link-accent">
                                    <?= e($song['album_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($song['album_name'] ?? 'Single') ?>
                            <?php endif; ?>
                            <br>
                            Year: <?= e($song['release_year'] ?: 'N/A') ?>
                            <?php if (!empty($song['genre_name'])): ?>
                                &middot; <?= e($song['genre_name']) ?>
                            <?php endif; ?>
                        </p>

                        <?php if (!empty($song['music_file'])): ?>
                            <audio controls preload="none" class="w-100 mt-auto">
                                <source src="<?= e($song['music_file']) ?>">
                            </audio>
                        <?php endif; ?>

                        <a href="music_details.php?id=<?= (int)$song['id'] ?>" class="btn btn-primary w-100 mt-3">
                            Details &amp; Reviews
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted">No music tracks available yet.</div>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
