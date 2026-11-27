<?php
require_once "config/db.php";

$pageTitle = "Search - SOUND Entertainment";
$basePath = "";
include "includes/header.php";

$keyword = trim($_GET['q'] ?? '');
$typeFilter = $_GET['type'] ?? 'all';
if (!in_array($typeFilter, ['all', 'music', 'video'], true)) {
    $typeFilter = 'all';
}
$artist = (int)($_GET['artist'] ?? 0);
$album = (int)($_GET['album'] ?? 0);
$year = trim($_GET['year'] ?? '');
$genre = (int)($_GET['genre'] ?? 0);
$language = (int)($_GET['language'] ?? 0);

$artists = $conn->query("SELECT id, artist_name FROM artists ORDER BY artist_name");
$albums = $conn->query("SELECT id, album_name FROM albums ORDER BY album_name");
$genres = $conn->query("SELECT id, genre_name FROM genres ORDER BY genre_name");
$languages = $conn->query("SELECT id, language_name FROM languages ORDER BY language_name");

$musicQuery = "
SELECT m.id, m.title, m.music_file AS media_file, m.cover_image AS thumb,
       m.artist_id, m.album_id, a.artist_name, al.album_name, m.release_year,
       g.genre_name, l.language_name, 'music' AS type
FROM music m
LEFT JOIN artists a ON m.artist_id = a.id
LEFT JOIN albums al ON m.album_id = al.id
LEFT JOIN genres g ON m.genre_id = g.id
LEFT JOIN languages l ON m.language_id = l.id
WHERE (? = '' OR m.title LIKE CONCAT('%', ?, '%')
       OR a.artist_name LIKE CONCAT('%', ?, '%')
       OR al.album_name LIKE CONCAT('%', ?, '%'))
AND (? = 0 OR m.artist_id = ?)
AND (? = 0 OR m.album_id = ?)
AND (? = '' OR m.release_year = ?)
AND (? = 0 OR m.genre_id = ?)
AND (? = 0 OR m.language_id = ?)
";

$videoQuery = "
SELECT v.id, v.title, v.video_file AS media_file, v.thumbnail AS thumb,
       v.artist_id, v.album_id, a.artist_name, al.album_name, v.release_year,
       g.genre_name, l.language_name, 'video' AS type
FROM videos v
LEFT JOIN artists a ON v.artist_id = a.id
LEFT JOIN albums al ON v.album_id = al.id
LEFT JOIN genres g ON v.genre_id = g.id
LEFT JOIN languages l ON v.language_id = l.id
WHERE (? = '' OR v.title LIKE CONCAT('%', ?, '%')
       OR a.artist_name LIKE CONCAT('%', ?, '%')
       OR al.album_name LIKE CONCAT('%', ?, '%'))
AND (? = 0 OR v.artist_id = ?)
AND (? = 0 OR v.album_id = ?)
AND (? = '' OR v.release_year = ?)
AND (? = 0 OR v.genre_id = ?)
AND (? = 0 OR v.language_id = ?)
";

$singleTypes = "ssssiiiissiiii";
$singleParams = [
    $keyword, $keyword, $keyword, $keyword,
    $artist, $artist,
    $album, $album,
    $year, $year,
    $genre, $genre,
    $language, $language
];

if ($typeFilter === 'music') {
    $sql = $musicQuery . " ORDER BY title ASC";
    $types = $singleTypes;
    $params = $singleParams;
} elseif ($typeFilter === 'video') {
    $sql = $videoQuery . " ORDER BY title ASC";
    $types = $singleTypes;
    $params = $singleParams;
} else {
    $sql = "($musicQuery) UNION ALL ($videoQuery) ORDER BY title ASC";
    $types = $singleTypes . $singleTypes;
    $params = array_merge($singleParams, $singleParams);
}

$results = null;
$queryError = "";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $results = $stmt->get_result();
    } else {
        $queryError = "Could not run search right now.";
    }
} else {
    $queryError = "Search error: " . $conn->error;
}
?>

<div class="mb-4">
    <h1 class="fw-bold mb-1">Search Music &amp; Videos</h1>
    <p class="text-muted mb-0">Filter by keyword, artist, album, year, genre, or language.</p>
</div>

<form method="GET" action="search.php" class="card p-4 mb-5">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Keyword</label>
            <input type="text" name="q" class="form-control" placeholder="Song, video, artist..."
                   value="<?= e($keyword) ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="music" <?= $typeFilter === 'music' ? 'selected' : '' ?>>Music</option>
                <option value="video" <?= $typeFilter === 'video' ? 'selected' : '' ?>>Videos</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Artist</label>
            <select name="artist" class="form-select">
                <option value="0">All Artists</option>
                <?php if ($artists): ?>
                    <?php while ($a = $artists->fetch_assoc()): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= $artist === (int)$a['id'] ? 'selected' : '' ?>>
                            <?= e($a['artist_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Album</label>
            <select name="album" class="form-select">
                <option value="0">All Albums</option>
                <?php if ($albums): ?>
                    <?php while ($a = $albums->fetch_assoc()): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= $album === (int)$a['id'] ? 'selected' : '' ?>>
                            <?= e($a['album_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" min="1900" max="2155"
                   placeholder="e.g. 2026" value="<?= e($year) ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Genre</label>
            <select name="genre" class="form-select">
                <option value="0">All Genres</option>
                <?php if ($genres): ?>
                    <?php while ($g = $genres->fetch_assoc()): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= $genre === (int)$g['id'] ? 'selected' : '' ?>>
                            <?= e($g['genre_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Language</label>
            <select name="language" class="form-select">
                <option value="0">All Languages</option>
                <?php if ($languages): ?>
                    <?php while ($l = $languages->fetch_assoc()): ?>
                        <option value="<?= (int)$l['id'] ?>" <?= $language === (int)$l['id'] ? 'selected' : '' ?>>
                            <?= e($l['language_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button type="submit" class="btn btn-primary px-4">Search</button>
            <a href="search.php" class="btn btn-outline-light px-4">Reset</a>
        </div>
    </div>
</form>

<?php if ($queryError !== ''): ?>
    <div class="alert alert-danger"><?= e($queryError) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Results</h3>
    <?php if ($results): ?>
        <span class="text-muted small"><?= $results->num_rows ?> found</span>
    <?php endif; ?>
</div>

<div class="row g-4">
    <?php if ($results && $results->num_rows > 0): ?>
        <?php while ($item = $results->fetch_assoc()): ?>
            <?php
            $isMusic = $item['type'] === 'music';
            $detailUrl = ($isMusic ? 'music_details.php?id=' : 'video_details.php?id=') . (int)$item['id'];
            $fallback = $isMusic ? 'assets/images/music-placeholder.svg' : 'assets/images/video-placeholder.svg';
            $img = !empty($item['thumb']) ? $item['thumb'] : $fallback;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-item h-100 d-flex flex-column">
                    <a href="<?= $detailUrl ?>">
                        <img src="<?= e($img) ?>" class="card-img-top" alt="<?= e($item['title']) ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge <?= $isMusic ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                <?= ucfirst($item['type']) ?>
                            </span>
                            <?php if (!empty($item['release_year'])): ?>
                                <span class="text-muted small"><?= e($item['release_year']) ?></span>
                            <?php endif; ?>
                        </div>

                        <h5 class="card-title">
                            <a href="<?= $detailUrl ?>"><?= e($item['title']) ?></a>
                        </h5>

                        <p class="text-muted small mb-3">
                            Artist:
                            <?php if (!empty($item['artist_id']) && !empty($item['artist_name'])): ?>
                                <a href="artist_details.php?id=<?= (int)$item['artist_id'] ?>" class="link-accent">
                                    <?= e($item['artist_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($item['artist_name'] ?? 'Unknown') ?>
                            <?php endif; ?>
                            <br>
                            Album:
                            <?php if (!empty($item['album_id']) && !empty($item['album_name'])): ?>
                                <a href="album_details.php?id=<?= (int)$item['album_id'] ?>" class="link-accent">
                                    <?= e($item['album_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($item['album_name'] ?? 'Single') ?>
                            <?php endif; ?>
                            <br>
                            <?= e($item['genre_name'] ?? 'N/A') ?> &middot; <?= e($item['language_name'] ?? 'N/A') ?>
                        </p>

                        <?php if (!empty($item['media_file'])): ?>
                            <?php if ($isMusic): ?>
                                <audio controls preload="none" class="w-100 mt-auto">
                                    <source src="<?= e($item['media_file']) ?>">
                                </audio>
                            <?php else: ?>
                                <video controls preload="none" class="w-100 mt-auto">
                                    <source src="<?= e($item['media_file']) ?>">
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>

                        <a href="<?= $detailUrl ?>" class="btn btn-primary w-100 mt-3">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted">
                No matching music or videos found.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
if ($stmt) {
    $stmt->close();
}
include "includes/footer.php";
?>
