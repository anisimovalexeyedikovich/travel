<?php
require_once '../config.php';

if (!checkAuth() || !isAdmin()) {
    redirect('/');
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$total = $conn->query("SELECT COUNT(*) as count FROM travels")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

// Get travels with stats
$travels = $conn->query("
    SELECT t.*, 
           u.username,
           COUNT(DISTINCT p.id) as photos_count,
           COUNT(DISTINCT c.id) as cultural_sites_count,
           (SELECT photo_url FROM photos WHERE travel_id = t.id AND is_main = 1 LIMIT 1) as main_photo
    FROM travels t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN photos p ON t.id = p.travel_id
    LEFT JOIN cultural_sites c ON t.id = c.travel_id
    GROUP BY t.id
    ORDER BY t.created_at DESC
    LIMIT $offset, $per_page
");

// Handle travel deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_travel'])) {
    $travel_id = intval($_POST['delete_travel']);
    $conn->query("DELETE FROM travels WHERE id = $travel_id");
    redirect('/admin/travels.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление путешествиями | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Управление путешествиями</h2>
            <a href="/admin/index.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Назад к панели
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Превью</th>
                                <th>Название</th>
                                <th>Автор</th>
                                <th>Место</th>
                                <th>Стоимость</th>
                                <th>Фото</th>
                                <th>Места</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($travel = $travels->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $travel['id'] ?></td>
                                    <td>
                                        <img src="<?= $travel['main_photo'] ?? '/assets/img/default-travel.jpg' ?>" 
                                             alt="<?= $travel['title'] ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             class="rounded">
                                    </td>
                                    <td><?= $travel['title'] ?></td>
                                    <td><?= $travel['username'] ?></td>
                                    <td><?= $travel['location'] ?></td>
                                    <td><?= number_format($travel['total_cost'], 0) ?> RUB</td>
                                    <td><?= $travel['photos_count'] ?></td>
                                    <td><?= $travel['cultural_sites_count'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($travel['created_at'])) ?></td>
                                    <td>
                                        <a href="/travels/view.php?id=<?= $travel['id'] ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Вы уверены? Это действие нельзя отменить!');">
                                            <input type="hidden" name="delete_travel" 
                                                   value="<?= $travel['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Назад</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Вперед</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
