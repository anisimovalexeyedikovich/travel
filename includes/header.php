<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Дневник путешествий' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (strpos($_SERVER['REQUEST_URI'], '/travels/') !== false): ?>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=YOUR_API_KEY&lang=ru_RU" type="text/javascript"></script>
    <?php endif; ?>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <i class="bi bi-globe me-2"></i>
                <span>Дневник путешествий</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/travels/list.php">
                            <i class="bi bi-map me-1"></i>Путешествия
                        </a>
                    </li>
                    <?php if (checkAuth()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/travels/create.php">
                                <i class="bi bi-plus-circle me-1"></i>Создать запись
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/profile/index.php">
                                <i class="bi bi-person me-1"></i>Мой профиль
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/index.php">
                                <i class="bi bi-gear me-1"></i>Админ-панель
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav">
                    <?php if (checkAuth()): ?>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= $_SESSION['username'] ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile/index.php">Профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/auth/logout.php">Выйти</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="nav-link" href="/auth/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Войти
                        </a>
                        <a class="nav-link" href="/auth/register.php">
                            <i class="bi bi-person-plus me-1"></i>Регистрация
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
<style>
.navbar {
    background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
    padding: 1rem 0;
}

.navbar-brand {
    color: white !important;
    font-weight: 600;
    font-size: 1.3rem;
}

.navbar-brand i {
    font-size: 1.5rem;
}

.nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.nav-link:hover {
    color: white !important;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
}

.navbar-toggler {
    border-color: rgba(255, 255, 255, 0.5);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.7%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dropdown-item {
    padding: 0.5rem 1.5rem;
}

.dropdown-item:active {
    background-color: #1a56db;
}
</style>
