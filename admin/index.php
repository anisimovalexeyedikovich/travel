<?php
require_once '../config.php';

// Debug information
var_dump([
    'is_auth' => checkAuth(),
    'user_id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? null,
    'is_admin' => $_SESSION['is_admin'] ?? null
]);

// Проверяем авторизацию и права администратора
if (!checkAuth() || !isAdmin()) {
    redirect('/');
    exit;
}

// Get statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'travels' => $conn->query("SELECT COUNT(*) as count FROM travels")->fetch_assoc()['count'],
    'photos' => $conn->query("SELECT COUNT(*) as count FROM photos")->fetch_assoc()['count'],
    'sites' => $conn->query("SELECT COUNT(*) as count FROM cultural_sites")->fetch_assoc()['count']
];

// Get latest users
$latest_users = $conn->query("
    SELECT id, username, email, created_at, is_admin 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get latest travels
$latest_travels = $conn->query("
    SELECT t.*, u.username 
    FROM travels t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-4">
        <h1 class="mb-4">Админ-панель</h1>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Пользователи</h5>
                        <p class="card-text display-6"><?= $stats['users'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Путешествия</h5>
                        <p class="card-text display-6"><?= $stats['travels'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Фотографии</h5>
                        <p class="card-text display-6"><?= $stats['photos'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Культурные места</h5>
                        <p class="card-text display-6"><?= $stats['sites'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Latest Users -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Последние пользователи</h5>
                        <a href="/admin/users.php" class="btn btn-sm btn-primary">Все пользователи</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Имя</th>
                                        <th>Email</th>
                                        <th>Дата регистрации</th>
                                        <th>Роль</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latest_users as $user): ?>
                                        <tr>
                                            <td><?= $user['username'] ?></td>
                                            <td><?= $user['email'] ?></td>
                                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <?php if ($user['is_admin']): ?>
                                                    <span class="badge bg-primary">Админ</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Пользователь</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Travels -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Последние путешествия</h5>
                        <a href="/admin/travels.php" class="btn btn-sm btn-primary">Все путешествия</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Автор</th>
                                        <th>Место</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latest_travels as $travel): ?>
                                        <tr>
                                            <td><?= $travel['title'] ?></td>
                                            <td><?= $travel['username'] ?></td>
                                            <td><?= $travel['location'] ?></td>
                                            <td><?= date('d.m.Y', strtotime($travel['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
