<?php
require_once "config/db.php";

$pageTitle = "Albums - SOUND Entertainment";
$basePath = "";
include "includes/header.php";

$sql = "SELECT al.*, a.artist_name,
        (SELECT COUNT(*) FROM music m WHERE m.album_id = al.id) AS total_songs
        FROM albums al
        LEFT JOIN artists a ON al.artist_id = a.id
        ORDER BY al.id DESC";

$result = $conn->query($sql);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1">Albums</h1>
        <p class="text-muted mb-0">Browse full albums and tracklists.</p>
    </div>
    <a href="artists.php" class="btn btn-outline-accent">Browse Artists</a>
</div>

<div class="row g-4">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($album = $result->fetch_assoc()): ?>
            <?php $cover = !empty($album['cover_image']) ? $album['cover_image'] : 'assets/images/album-placeholder.svg'; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="album_details.php?id=<?= (int)$album['id'] ?>">
                        <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($album['album_name']) ?>">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2"><?= e($album['album_name']) ?></h5>
                        <p class="text-muted small mb-3">
                            Artist:
                            <?php if (!empty($album['artist_id']) && !empty($album['artist_name'])): ?>
                                <a href="artist_details.php?id=<?= (int)$album['artist_id'] ?>" class="link-accent">
                                    <?= e($album['artist_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($album['artist_name'] ?? 'Unknown') ?>
                            <?php endif; ?>
                            <br>
                            Year: <?= e($album['release_year'] ?: 'N/A') ?> &middot; <?= (int)$album['total_songs'] ?> tracks
                        </p>

                        <a href="album_details.php?id=<?= (int)$album['id'] ?>" class="btn btn-primary mt-auto w-100">
                            Explore Album
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted">No albums added yet.</div>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
