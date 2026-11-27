<?php
require_once "config/db.php";

$id = (int)($_GET['id'] ?? 0);
$artist = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM artists WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $artist = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$pageTitle = $artist ? ($artist['artist_name'] . " - SOUND") : "Not Found - SOUND";
$basePath = "";
if (!$artist) {
    http_response_code(404);
}
include "includes/header.php";

if (!$artist):
?>
    <div class="card p-5 text-center my-4">
        <h3 class="mb-2">Artist Not Found</h3>
        <p class="text-muted mb-4">This artist profile could not be found.</p>
        <div><a href="artists.php" class="btn btn-primary">Back to Artists</a></div>
    </div>
<?php
    include "includes/footer.php";
    exit;
endif;

$songStmt = $conn->prepare("SELECT * FROM music WHERE artist_id = ? ORDER BY created_at DESC");
$songStmt->bind_param("i", $id);
$songStmt->execute();
$songs = $songStmt->get_result();

$albumStmt = $conn->prepare("SELECT * FROM albums WHERE artist_id = ? ORDER BY release_year DESC, id DESC");
$albumStmt->bind_param("i", $id);
$albumStmt->execute();
$albums = $albumStmt->get_result();

$videoStmt = $conn->prepare("SELECT * FROM videos WHERE artist_id = ? ORDER BY created_at DESC");
$videoStmt->bind_param("i", $id);
$videoStmt->execute();
$videos = $videoStmt->get_result();

$img = !empty($artist['image']) ? $artist['image'] : 'assets/images/artist-placeholder.svg';
?>

<div class="mb-4">
    <a href="artists.php" class="btn btn-outline-light btn-sm">&larr; Back to Artists</a>
</div>

<div class="card p-4 mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-md-3 text-center">
            <img src="<?= e($img) ?>" class="rounded img-fluid"
                 style="width:200px;height:200px;object-fit:cover" alt="<?= e($artist['artist_name']) ?>">
        </div>
        <div class="col-md-9">
            <h1 class="fw-bold mb-2"><?= e($artist['artist_name']) ?></h1>
            <p class="text-muted small mb-3">
                <?= $songs->num_rows ?> Songs &middot;
                <?= $videos->num_rows ?> Videos &middot;
                <?= $albums->num_rows ?> Albums
            </p>
            <p class="mb-0">
                <?= !empty($artist['description']) ? nl2br(e($artist['description'])) : 'No biography available yet.' ?>
            </p>
        </div>
    </div>
</div>

<?php if ($albums->num_rows > 0): ?>
    <h3 class="section-heading mb-3">Albums</h3>
    <div class="row g-4 mb-5">
        <?php while ($album = $albums->fetch_assoc()): ?>
            <?php $cover = !empty($album['cover_image']) ? $album['cover_image'] : 'assets/images/album-placeholder.svg'; ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card card-item h-100">
                    <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($album['album_name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= e($album['album_name']) ?></h5>
                        <p class="text-muted small mb-3">Year: <?= e($album['release_year'] ?: 'N/A') ?></p>
                        <a href="album_details.php?id=<?= (int)$album['id'] ?>" class="btn btn-primary btn-sm mt-auto">
                            View Album
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<h3 class="section-heading mb-3">Music</h3>
<?php if ($songs->num_rows === 0): ?>
    <div class="card p-4 text-muted mb-5">No music available for this artist yet.</div>
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
    <h3 class="section-heading mb-3">Videos</h3>
    <div class="row g-4">
        <?php while ($video = $videos->fetch_assoc()): ?>
            <?php $thumb = !empty($video['thumbnail']) ? $video['thumbnail'] : 'assets/images/video-placeholder.svg'; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100">
                    <img src="<?= e($thumb) ?>" class="card-img-top" alt="<?= e($video['title']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= e($video['title']) ?></h5>
                        <p class="text-muted small mb-3">Year: <?= e($video['release_year'] ?: 'N/A') ?></p>
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
$albumStmt->close();
$videoStmt->close();
include "includes/footer.php";
?>
