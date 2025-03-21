<footer class="bg-dark text-white">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4">
                <h5>О проекте</h5>
                <p>Дневник путешествий - это платформа для путешественников, где вы можете делиться своими приключениями и открывать новые места.</p>
            </div>
            <div class="col-md-4">
                <h5>Навигация</h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="text-white">Главная</a></li>
                    <li><a href="/travels/list.php" class="text-white">Путешествия</a></li>
                    <?php if (checkAuth()): ?>
                        <li><a href="/profile/index.php" class="text-white">Мой профиль</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Контакты</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-envelope"></i> support@travel.ru</li>
                    <li><i class="bi bi-telephone"></i> +7 (999) 123-45-67</li>
                </ul>
                <div class="social-links mt-3">
                    <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white me-2"><i class="bi bi-telegram"></i></a>
                </div>
            </div>
        </div>
        <hr class="mt-4">
        <div class="text-center">
            <small>&copy; <?= date('Y') ?> Дневник путешествий. Все права защищены.</small>
        </div>
    </div>
</footer>
