<?php
require_once '../config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query with filters
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($_GET['location'])) {
    $where_conditions[] = "t.location = ?";
    $params[] = $_GET['location'];
    $param_types .= 's';
}

if (!empty($_GET['min_cost'])) {
    $where_conditions[] = "t.total_cost >= ?";
    $params[] = floatval($_GET['min_cost']);
    $param_types .= 'd';
}

if (!empty($_GET['max_cost'])) {
    $where_conditions[] = "t.total_cost <= ?";
    $params[] = floatval($_GET['max_cost']);
    $param_types .= 'd';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count with filters
$count_query = "SELECT COUNT(*) as count FROM travels t $where_clause";
if (!empty($params)) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['count'];
} else {
    $total = $conn->query($count_query)->fetch_assoc()['count'];
}

$total_pages = ceil($total / $per_page);

// Add sorting
$sort_order = "t.created_at DESC"; // default sorting
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'date_asc':
            $sort_order = "t.created_at ASC";
            break;
        case 'date_desc':
            $sort_order = "t.created_at DESC";
            break;
        case 'cost_desc':
            $sort_order = "t.total_cost DESC";
            break;
        case 'cost_asc':
            $sort_order = "t.total_cost ASC";
            break;
    }
}

// Get travels with filters
$query = "
    SELECT t.*, u.username, u.avatar,
           (SELECT photo_url FROM photos WHERE travel_id = t.id AND is_main = 1 LIMIT 1) as main_photo
    FROM travels t 
    JOIN users u ON t.user_id = u.id
    $where_clause
    ORDER BY $sort_order
    LIMIT ?, ?
";

// Add limit parameters
$params[] = $offset;
$params[] = $per_page;
$param_types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$travels = $stmt->get_result();

// Get filter options
$locations = $conn->query("SELECT DISTINCT location FROM travels ORDER BY location")->fetch_all(MYSQLI_ASSOC);
$cost_range = $conn->query("SELECT MIN(total_cost) as min, MAX(total_cost) as max FROM travels")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Путешествия | Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-5">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Место</label>
                                <select name="location" class="form-select">
                                    <option value="">Все места</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['location'] ?>" 
                                                <?= isset($_GET['location']) && $_GET['location'] === $loc['location'] ? 'selected' : '' ?>>
                                            <?= $loc['location'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Стоимость от</label>
                                <input type="number" class="form-control" name="min_cost" 
                                       value="<?= $_GET['min_cost'] ?? '' ?>" 
                                       min="<?= floor($cost_range['min']) ?>" 
                                       max="<?= ceil($cost_range['max']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Стоимость до</label>
                                <input type="number" class="form-control" name="max_cost" 
                                       value="<?= $_GET['max_cost'] ?? '' ?>"
                                       min="<?= floor($cost_range['min']) ?>" 
                                       max="<?= ceil($cost_range['max']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Сортировка</label>
                                <select name="sort" class="form-select">
                                    <option value="date_desc" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'date_desc') ? 'selected' : '' ?>>
                                        Сначала новые
                                    </option>
                                    <option value="date_asc" <?= isset($_GET['sort']) && $_GET['sort'] === 'date_asc' ? 'selected' : '' ?>>
                                        Сначала старые
                                    </option>
                                    <option value="cost_desc" <?= isset($_GET['sort']) && $_GET['sort'] === 'cost_desc' ? 'selected' : '' ?>>
                                        Дороже
                                    </option>
                                    <option value="cost_asc" <?= isset($_GET['sort']) && $_GET['sort'] === 'cost_asc' ? 'selected' : '' ?>>
                                        Дешевле
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="row g-4">
            <?php if ($travels->num_rows === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Путешествий не найдено. Попробуйте изменить параметры фильтра.
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
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <a href="/profile/view.php?id=<?= $travel['user_id'] ?>" class="text-decoration-none">
                                            <img src="<?= $travel['avatar'] ?>" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 24px; height: 24px; object-fit: cover;">
                                            <small class="text-muted"><?= $travel['username'] ?></small>
                                        </a>
                                    </div>
                                    <a href="/travels/view.php?id=<?= $travel['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">Подробнее</a>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= $travel['location'] ?>
                                    </small>
                                    <small class="text-muted">
                                        <?= number_format($travel['total_cost'], 0) ?> RUB
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
