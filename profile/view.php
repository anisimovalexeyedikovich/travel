<?php
require_once '../config.php';

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user info
$stmt = $conn->prepare("
    SELECT id, username, avatar, created_at, 
           (SELECT COUNT(*) FROM travels WHERE user_id = users.id) as travels_count
    FROM users 
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    redirect('/');
}

// Get user's travels
$stmt = $conn->prepare("
    SELECT t.*, 
           (SELECT photo_url FROM photos WHERE travel_id = t.id AND is_main = 1 LIMIT 1) as main_photo,
           (SELECT COUNT(*) FROM photos WHERE travel_id = t.id) as photos_count
    FROM travels t 
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$travels = $stmt->get_result();

// Get total stats
$stats = $conn->prepare("
    SELECT 
        COUNT(DISTINCT t.location) as unique_locations,
        SUM(t.total_cost) as total_spent,
        COUNT(*) as total_travels,
        (SELECT COUNT(*) FROM photos p JOIN travels tt ON p.travel_id = tt.id WHERE tt.user_id = ?) as total_photos
    FROM travels t 
    WHERE t.user_id = ?
");
$stats->bind_param("ii", $user_id, $user_id);
$stats->execute();
$user_stats = $stats->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль <?= $user['username'] ?> | Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-4">
        <!-- User Profile Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-dark text-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <img src="<?= $user['avatar'] ?? '/assets/img/default-avatar.jpg' ?>" 
                                 class="rounded-circle me-4" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                            <div>
                                <h1 class="mb-2"><?= $user['username'] ?></h1>
                                <p class="mb-0">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    На сайте с <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Путешествий</h5>
                        <p class="display-6 mb-0"><?= $user_stats['total_travels'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Уникальных мест</h5>
                        <p class="display-6 mb-0"><?= $user_stats['unique_locations'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Фотографий</h5>
                        <p class="display-6 mb-0"><?= $user_stats['total_photos'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Потрачено</h5>
                        <p class="display-6 mb-0"><?= number_format($user_stats['total_spent'], 0) ?> ₽</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Travels -->
        <h2 class="mb-4">Путешествия</h2>
        <div class="row g-4">
            <?php if ($travels->num_rows === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        У пользователя пока нет путешествий
                    </div>
                </div>
            <?php else: ?>
                <?php while ($travel = $travels->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <img src="<?= $travel['main_photo'] ?? '/assets/img/default-travel.jpg' ?>" 
                                 class="card-img-top" alt="<?= $travel['title'] ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= $travel['title'] ?></h5>
                                <p class="card-text"><?= substr($travel['description'], 0, 100) ?>...</p>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> <?= $travel['location'] ?>
                                        </small>
                                    </div>
                                    <div>
                                        <small class="text-muted me-3">
                                            <i class="bi bi-camera"></i> <?= $travel['photos_count'] ?>
                                        </small>
                                        <a href="/travels/view.php?id=<?= $travel['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            Подробнее
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
