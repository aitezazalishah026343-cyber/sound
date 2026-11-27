<?php
require_once "config/db.php";

$id = (int)($_GET['id'] ?? 0);
$album = null;

if ($id > 0) {
    $stmt = $conn->prepare(
        "SELECT al.*, a.artist_name
         FROM albums al
         LEFT JOIN artists a ON al.artist_id = a.id
         WHERE al.id = ?"
    );
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $album = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$pageTitle = $album ? ($album['album_name'] . " - SOUND") : "Not Found - SOUND";
$basePath = "";
if (!$album) {
    http_response_code(404);
}
include "includes/header.php";

if (!$album):
?>
    <div class="card p-5 text-center my-4">
        <h3 class="mb-2">Album Not Found</h3>
        <p class="text-muted mb-4">This album could not be found.</p>
        <div><a href="albums.php" class="btn btn-primary">Back to Albums</a></div>
    </div>
<?php
    include "includes/footer.php";
    exit;
endif;

$songStmt = $conn->prepare("SELECT * FROM music WHERE album_id = ? ORDER BY title ASC");
$songStmt->bind_param("i", $id);
$songStmt->execute();
$songs = $songStmt->get_result();

$videoStmt = $conn->prepare("SELECT * FROM videos WHERE album_id = ? ORDER BY title ASC");
$videoStmt->bind_param("i", $id);
$videoStmt->execute();
$videos = $videoStmt->get_result();

$cover = !empty($album['cover_image']) ? $album['cover_image'] : 'assets/images/album-placeholder.svg';
?>

<div class="mb-4">
    <a href="albums.php" class="btn btn-outline-light btn-sm">&larr; Back to Albums</a>
</div>

<div class="card p-4 mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-md-3 text-center">
            <img src="<?= e($cover) ?>" class="rounded img-fluid"
                 style="width:200px;height:200px;object-fit:cover" alt="<?= e($album['album_name']) ?>">
        </div>
        <div class="col-md-9">
            <h1 class="fw-bold mb-2"><?= e($album['album_name']) ?></h1>
            <p class="text-muted mb-1">
                Artist:
                <?php if (!empty($album['artist_id']) && !empty($album['artist_name'])): ?>
                    <a href="artist_details.php?id=<?= (int)$album['artist_id'] ?>" class="link-accent"><?= e($album['artist_name']) ?></a>
                <?php else: ?>
                    <?= e($album['artist_name'] ?? 'Unknown') ?>
                <?php endif; ?>
            </p>
            <p class="text-muted mb-0">
                Year: <?= e($album['release_year'] ?: 'N/A') ?> &middot; <?= $songs->num_rows ?> tracks
            </p>
        </div>
    </div>
</div>

<h3 class="section-heading mb-3">Tracks</h3>
<?php if ($songs->num_rows === 0): ?>
    <div class="card p-4 text-muted mb-4">No songs in this album yet.</div>
<?php else: ?>
    <div class="row g-3 mb-5">
        <?php while ($song = $songs->fetch_assoc()): ?>
            <div class="col-md-6">
                <div class="card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <a href="music_details.php?id=<?= (int)$song['id'] ?>"><?= e($song['title']) ?></a>
                        </h5>
                        <a href="music_details.php?id=<?= (int)$song['id'] ?>" class="btn btn-outline-accent btn-sm">
                            Details
                        </a>
                    </div>
                    <?php if (!empty($song['music_file'])): ?>
                        <audio controls preload="none" class="w-100">
                            <source src="<?= e($song['music_file']) ?>">
                        </audio>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php if ($videos->num_rows > 0): ?>
    <h3 class="section-heading mb-3">Album Videos</h3>
    <div class="row g-4">
        <?php while ($video = $videos->fetch_assoc()): ?>
            <?php $thumb = !empty($video['thumbnail']) ? $video['thumbnail'] : 'assets/images/video-placeholder.svg'; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100">
                    <img src="<?= e($thumb) ?>" class="card-img-top" alt="<?= e($video['title']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= e($video['title']) ?></h5>
                        <a href="video_details.php?id=<?= (int)$video['id'] ?>" class="btn btn-primary btn-sm mt-auto">
                            Watch Video
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php
$songStmt->close();
$videoStmt->close();
include "includes/footer.php";
?>
