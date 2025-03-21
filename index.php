<?php
require_once 'config.php';

$latest_travels = $conn->query("
    SELECT t.*, u.username, p.photo_url as main_photo 
    FROM travels t 
    JOIN users u ON t.user_id = u.id 
    LEFT JOIN photos p ON t.id = p.travel_id AND p.is_main = 1 
    ORDER BY t.created_at DESC 
    LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero-section text-white text-center">
            <div class="container">
                <h1 class="display-4 fw-bold">Дневник путешествий</h1>
                <p class="lead mb-4">Сохраняйте воспоминания о ваших путешествиях и делитесь ими с другими</p>
                <?php if (!checkAuth()): ?>
                    <a href="/auth/register.php" class="btn btn-primary btn-lg me-2">Регистрация</a>
                    <a href="/auth/login.php" class="btn btn-outline-light btn-lg">Вход</a>
                <?php else: ?>
                    <a href="/travels/create.php" class="btn btn-primary btn-lg">Создать запись</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Latest Travels -->
        <section class="latest-travels py-5">
            <div class="container">
                <h2 class="text-center mb-4">Последние путешествия</h2>
                <div class="row g-4">
                    <?php while ($travel = $latest_travels->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card h-100 travel-card">
                                <img src="<?= $travel['main_photo'] ?? '/assets/img/default-travel.jpg' ?>" 
                                     class="card-img-top" alt="<?= $travel['title'] ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $travel['title'] ?></h5>
                                    <p class="card-text"><?= substr($travel['description'], 0, 100) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> <?= $travel['username'] ?>
                                        </small>
                                        <a href="/travels/view.php?id=<?= $travel['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">Подробнее</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="/travels/list.php" class="btn btn-outline-primary">Смотреть все путешествия</a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section py-5 bg-light">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <i class="bi bi-geo-alt display-4 text-primary"></i>
                            <h3>Геолокация</h3>
                            <p>Отмечайте места своих путешествий на карте</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <i class="bi bi-camera display-4 text-primary"></i>
                            <h3>Фотографии</h3>
                            <p>Загружайте фото из ваших поездок</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <i class="bi bi-share display-4 text-primary"></i>
                            <h3>Делитесь впечатлениями</h3>
                            <p>Рассказывайте о своих приключениях</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
