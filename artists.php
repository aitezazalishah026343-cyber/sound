<?php
require_once "config/db.php";

$pageTitle = "Artists - SOUND Entertainment";
$basePath = "";
include "includes/header.php";

$sql = "SELECT a.*,
        (SELECT COUNT(*) FROM music m WHERE m.artist_id = a.id) AS total_songs,
        (SELECT COUNT(*) FROM videos v WHERE v.artist_id = a.id) AS total_videos,
        (SELECT COUNT(*) FROM albums al WHERE al.artist_id = a.id) AS total_albums
        FROM artists a
        ORDER BY a.artist_name ASC";

$result = $conn->query($sql);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1">Artists</h1>
        <p class="text-muted mb-0">Explore musicians and creators on SOUND.</p>
    </div>
    <a href="albums.php" class="btn btn-outline-accent">Browse Albums</a>
</div>

<div class="row g-4">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($artist = $result->fetch_assoc()): ?>
            <?php $img = !empty($artist['image']) ? $artist['image'] : 'assets/images/artist-placeholder.svg'; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="artist_details.php?id=<?= (int)$artist['id'] ?>">
                        <img src="<?= e($img) ?>" class="card-img-top" alt="<?= e($artist['artist_name']) ?>">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2"><?= e($artist['artist_name']) ?></h5>
                        <p class="text-muted small mb-2">
                            <?= (int)$artist['total_songs'] ?> Songs &middot;
                            <?= (int)$artist['total_videos'] ?> Videos &middot;
                            <?= (int)$artist['total_albums'] ?> Albums
                        </p>

                        <?php if (!empty($artist['description'])): ?>
                            <p class="text-muted small mb-3"><?= e(textSnippet($artist['description'], 115)) ?></p>
                        <?php endif; ?>

                        <a href="artist_details.php?id=<?= (int)$artist['id'] ?>" class="btn btn-primary mt-auto w-100">
                            Explore Artist
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted">No artists added yet.</div>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
