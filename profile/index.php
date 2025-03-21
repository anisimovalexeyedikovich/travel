<?php
require_once '../config.php';

if (!checkAuth()) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's travels
$stmt = $conn->prepare("
    SELECT t.*, 
           (SELECT photo_url FROM photos WHERE travel_id = t.id AND is_main = 1 LIMIT 1) as main_photo,
           COUNT(DISTINCT p.id) as photos_count,
           COUNT(DISTINCT c.id) as cultural_sites_count
    FROM travels t
    LEFT JOIN photos p ON t.id = p.travel_id
    LEFT JOIN cultural_sites c ON t.id = c.travel_id
    WHERE t.user_id = ?
    GROUP BY t.id
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$travels = $stmt->get_result();

// Handle profile update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;

    if ($exists) {
        $error = 'Пользователь с таким именем или email уже существует';
    } else {
        // If changing password
        if (!empty($current_password)) {
            if ($current_password !== $user['password']) { // В реальном проекте используйте password_verify()
                $error = 'Неверный текущий пароль';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Новые пароли не совпадают';
            } else {
                $password = $new_password;
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $password, $user_id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $success = 'Профиль успешно обновлен';
            
            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['avatar']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_path = '../uploads/avatars/';
                    
                    if (!file_exists($upload_path)) {
                        mkdir($upload_path, 0777, true);
                    }
                    
                    $file_name = uniqid() . '_' . $_FILES['avatar']['name'];
                    $file_path = $upload_path . $file_name;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
                        $avatar_url = '/uploads/avatars/' . $file_name;
                        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $stmt->bind_param("si", $avatar_url, $user_id);
                        $stmt->execute();
                    }
                }
            }
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Ошибка при обновлении профиля';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль | Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-5">
        <div class="row">
            <!-- Profile Info -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?= $user['avatar'] ?>" alt="<?= $user['username'] ?>" 
                             class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <h4><?= $user['username'] ?></h4>
                        <p class="text-muted"><?= $user['email'] ?></p>
                        <p>
                            <small class="text-muted">
                                Дата регистрации: <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                            </small>
                        </p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            Редактировать профиль
                        </button>
                    </div>
                </div>
            </div>

            <!-- User's Travels -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Мои путешествия</h3>
                    <a href="/travels/create.php" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Создать запись
                    </a>
                </div>

                <?php if ($travels->num_rows === 0): ?>
                    <div class="alert alert-info">
                        У вас пока нет записей о путешествиях. 
                        <a href="/travels/create.php">Создайте первую запись</a>!
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php while ($travel = $travels->fetch_assoc()): ?>
                            <div class="col-md-6">
                                <div class="card h-100 travel-card">
                                    <img src="<?= $travel['main_photo'] ?? '/assets/img/default-travel.jpg' ?>" 
                                         class="card-img-top" alt="<?= $travel['title'] ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $travel['title'] ?></h5>
                                        <p class="card-text"><?= substr($travel['description'], 0, 100) ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted">
                                                <small><i class="bi bi-camera"></i> <?= $travel['photos_count'] ?></small>
                                                <small class="ms-2"><i class="bi bi-geo-alt"></i> <?= $travel['cultural_sites_count'] ?></small>
                                            </div>
                                            <a href="/travels/view.php?id=<?= $travel['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">Подробнее</a>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">
                                            <?= date('d.m.Y', strtotime($travel['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать профиль</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Аватар</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Имя пользователя</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= $user['username'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= $user['email'] ?>" required>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <small class="text-muted">Заполните, только если хотите изменить пароль</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Сохранить изменения</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
