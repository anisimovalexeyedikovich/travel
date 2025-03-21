<?php
require_once '../config.php';

if (!checkAuth() || !isAdmin()) {
    redirect('/');
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

// Get users with their stats
$users = $conn->query("
    SELECT u.*, 
           COUNT(DISTINCT t.id) as travels_count,
           COUNT(DISTINCT p.id) as photos_count
    FROM users u
    LEFT JOIN travels t ON u.id = t.user_id
    LEFT JOIN photos p ON t.id = p.travel_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $offset, $per_page
");

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['delete_user']);
    if ($user_id !== $_SESSION['user_id']) { // Prevent admin from deleting themselves
        $conn->query("DELETE FROM users WHERE id = $user_id");
        redirect('/admin/users.php');
    }
}

// Handle user role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_admin'])) {
    $user_id = intval($_POST['toggle_admin']);
    if ($user_id !== $_SESSION['user_id']) { // Prevent admin from changing their own role
        $current_role = $conn->query("SELECT is_admin FROM users WHERE id = $user_id")->fetch_assoc()['is_admin'];
        $new_role = $current_role ? 0 : 1;
        $conn->query("UPDATE users SET is_admin = $new_role WHERE id = $user_id");
        redirect('/admin/users.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Управление пользователями</h2>
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
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Путешествия</th>
                                <th>Фото</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $user['avatar'] ?>" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                            <?= $user['username'] ?>
                                        </div>
                                    </td>
                                    <td><?= $user['email'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $user['is_admin'] ? 'primary' : 'secondary' ?>">
                                            <?= $user['is_admin'] ? 'Админ' : 'Пользователь' ?>
                                        </span>
                                    </td>
                                    <td><?= $user['travels_count'] ?></td>
                                    <td><?= $user['photos_count'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Вы уверены?');">
                                                <input type="hidden" name="toggle_admin" 
                                                       value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-<?= $user['is_admin'] ? 'warning' : 'success' ?>">
                                                    <?= $user['is_admin'] ? 'Снять админа' : 'Сделать админом' ?>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Вы уверены? Это действие нельзя отменить!');">
                                                <input type="hidden" name="delete_user" 
                                                       value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
