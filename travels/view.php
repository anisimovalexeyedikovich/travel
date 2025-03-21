<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    redirect('/');
}

$travel_id = intval($_GET['id']);

// Get travel details
$stmt = $conn->prepare("
    SELECT t.*, u.username, u.avatar 
    FROM travels t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.id = ?
");
$stmt->bind_param("i", $travel_id);
$stmt->execute();
$travel = $stmt->get_result()->fetch_assoc();

if (!$travel) {
    redirect('/');
}

// Get photos
$stmt = $conn->prepare("SELECT * FROM photos WHERE travel_id = ?");
$stmt->bind_param("i", $travel_id);
$stmt->execute();
$photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get cultural sites
$stmt = $conn->prepare("SELECT * FROM cultural_sites WHERE travel_id = ?");
$stmt->bind_param("i", $travel_id);
$stmt->execute();
$cultural_sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get main photo
$main_photo = '/assets/img/default-travel.jpg';
foreach ($photos as $photo) {
    if ($photo['is_main']) {
        $main_photo = $photo['photo_url'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $travel['title'] ?> | Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="travel-hero position-relative">
            <img src="<?= $main_photo ?>" alt="<?= $travel['title'] ?>" class="w-100" style="height: 400px; object-fit: cover;">
            <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                <div class="container">
                    <h1 class="text-white mb-2"><?= $travel['title'] ?></h1>
                    <div class="d-flex align-items-center text-white">
                        <a href="/profile/view.php?id=<?= $travel['user_id'] ?>" class="text-decoration-none d-flex align-items-center">
                            <img src="<?= $travel['avatar'] ?>" 
                                 class="rounded-circle me-2" 
                                 style="width: 32px; height: 32px; object-fit: cover;">
                            <span><?= $travel['username'] ?></span>
                        </a>
                        <span class="mx-2">•</span>
                        <span><?= date('d.m.Y', strtotime($travel['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container py-5">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Description -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">О путешествии</h5>
                            <p class="card-text"><?= nl2br($travel['description']) ?></p>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <p><i class="bi bi-geo-alt"></i> <?= $travel['location'] ?></p>
                                    <p><i class="bi bi-calendar"></i> <?= date('d.m.Y', strtotime($travel['start_date'])) ?> - <?= date('d.m.Y', strtotime($travel['end_date'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><?= number_format($travel['total_cost'], 2) ?> RUB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Местоположение</h5>
                            <div id="travel-map" class="map-container" 
                                 style="height: 400px;"
                                 data-lat="<?= $travel['latitude'] ?>" 
                                 data-lng="<?= $travel['longitude'] ?>"
                                 data-title="<?= htmlspecialchars($travel['location']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Фотографии</h5>
                            <div class="row g-3">
                                <?php foreach ($photos as $photo): ?>
                                    <div class="col-md-4">
                                        <img src="<?= $photo['photo_url'] ?>" 
                                             alt="<?= $photo['description'] ?>"
                                             class="gallery-image w-100"
                                             data-bs-toggle="modal"
                                             data-bs-target="#imageModal">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cultural Sites -->
                    <?php if (!empty($cultural_sites)): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Культурные места</h5>
                                <div class="row">
                                    <?php foreach ($cultural_sites as $site): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="cultural-site-card">
                                                <img src="<?= $site['photo_url'] ?>" 
                                                     alt="<?= $site['name'] ?>" 
                                                     class="w-100">
                                                <div class="p-3">
                                                    <h6><?= $site['name'] ?></h6>
                                                    <p class="small text-muted mb-0"><?= $site['description'] ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <!-- Ratings -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Оценки</h5>
                            <div class="mb-3">
                                <label class="d-flex justify-content-between">
                                    <span>Безопасность</span>
                                    <span class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $travel['safety_rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="d-flex justify-content-between">
                                    <span>Транспорт</span>
                                    <span class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $travel['transport_rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="d-flex justify-content-between">
                                    <span>Населенность</span>
                                    <span class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $travel['population_rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="d-flex justify-content-between">
                                    <span>Природа</span>
                                    <span class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $travel['nature_rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Share -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Поделиться</h5>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-outline-success">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-telegram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" 
                            data-bs-dismiss="modal"></button>
                    <img src="" class="modal-img w-100">
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
