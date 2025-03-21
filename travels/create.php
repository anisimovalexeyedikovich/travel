<?php
require_once '../config.php';

if (!checkAuth()) {
    redirect('/auth/login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $location = sanitize($_POST['location']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_cost = floatval($_POST['total_cost']);
    $safety_rating = intval($_POST['safety_rating']);
    $transport_rating = intval($_POST['transport_rating']);
    $population_rating = intval($_POST['population_rating']);
    $nature_rating = intval($_POST['nature_rating']);

    $stmt = $conn->prepare("INSERT INTO travels (user_id, title, description, location, latitude, longitude, 
                           start_date, end_date, total_cost, safety_rating, transport_rating, 
                           population_rating, nature_rating) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isssddssdiiii", 
        $_SESSION['user_id'], $title, $description, $location, $latitude, $longitude,
        $start_date, $end_date, $total_cost, $safety_rating, $transport_rating,
        $population_rating, $nature_rating
    );

    if ($stmt->execute()) {
        $travel_id = $stmt->insert_id;

        // Handle photo uploads
        if (isset($_FILES['photos'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $upload_path = '../uploads/photos/';
            
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_type = $_FILES['photos']['type'][$key];
                    
                    if (in_array($file_type, $allowed_types)) {
                        $file_name = uniqid() . '_' . $_FILES['photos']['name'][$key];
                        $file_path = $upload_path . $file_name;
                        
                        if (move_uploaded_file($tmp_name, $file_path)) {
                            $is_main = isset($_POST['main_photo']) && $_POST['main_photo'] == $key;
                            $photo_desc = sanitize($_POST['photo_descriptions'][$key] ?? '');
                            
                            $stmt = $conn->prepare("INSERT INTO photos (travel_id, photo_url, description, is_main) VALUES (?, ?, ?, ?)");
                            $photo_url = '/uploads/photos/' . $file_name;
                            $stmt->bind_param("issi", $travel_id, $photo_url, $photo_desc, $is_main);
                            $stmt->execute();
                        }
                    }
                }
            }
        }

        // Handle cultural sites
        if (isset($_POST['cultural_sites'])) {
            foreach ($_POST['cultural_sites'] as $site) {
                if (!empty($site['name'])) {
                    $site_name = sanitize($site['name']);
                    $site_desc = sanitize($site['description']);
                    $site_photo = sanitize($site['photo_url']);
                    
                    $stmt = $conn->prepare("INSERT INTO cultural_sites (travel_id, name, description, photo_url) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $travel_id, $site_name, $site_desc, $site_photo);
                    $stmt->execute();
                }
            }
        }

        $success = 'Путешествие успешно создано!';
        redirect('/travels/view.php?id=' . $travel_id);
    } else {
        $error = 'Ошибка при создании путешествия';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать путешествие | Дневник путешествий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Создать новое путешествие</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateTravelForm()">
                            <div class="mb-3">
                                <label for="title" class="form-label">Название путешествия</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Описание</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Местоположение</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Координаты</label>
                                <div id="travel-map" class="map-container" 
                                     style="height: 400px;"
                                     data-editable="true"
                                     data-lat="55.7558"
                                     data-lng="37.6173">
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" id="latitude" name="latitude" 
                                               step="any" placeholder="Широта" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" id="longitude" name="longitude" 
                                               step="any" placeholder="Долгота" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Дата начала</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">Дата окончания</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="total_cost" class="form-label">Общая стоимость (RUB)</label>
                                <input type="number" class="form-control" id="total_cost" name="total_cost" 
                                       step="0.01" required>
                            </div>

                            <div class="mb-4">
                                <h5>Оценки</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Безопасность</label>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <input type="radio" name="safety_rating" value="<?= $i ?>" 
                                                       class="rating-input" <?= $i === 3 ? 'checked' : '' ?>>
                                                <i class="bi bi-star-fill rating-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Транспорт</label>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <input type="radio" name="transport_rating" value="<?= $i ?>" 
                                                       class="rating-input" <?= $i === 3 ? 'checked' : '' ?>>
                                                <i class="bi bi-star-fill rating-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Населенность</label>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <input type="radio" name="population_rating" value="<?= $i ?>" 
                                                       class="rating-input" <?= $i === 3 ? 'checked' : '' ?>>
                                                <i class="bi bi-star-fill rating-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Природа</label>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <input type="radio" name="nature_rating" value="<?= $i ?>" 
                                                       class="rating-input" <?= $i === 3 ? 'checked' : '' ?>>
                                                <i class="bi bi-star-fill rating-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5>Фотографии</h5>
                                <div id="photo-container">
                                    <div class="photo-item mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="file" class="form-control image-upload" 
                                                       name="photos[]" accept="image/*">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" 
                                                       name="photo_descriptions[]" placeholder="Описание">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" 
                                                           name="main_photo" value="0">
                                                    <label class="form-check-label">Главное фото</label>
                                                </div>
                                            </div>
                                        </div>
                                        <img class="image-preview mt-2" style="display: none; max-width: 200px;">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="addPhotoInput()">
                                    <i class="bi bi-plus"></i> Добавить фото
                                </button>
                            </div>

                            <div class="mb-4">
                                <h5>Культурные места</h5>
                                <div id="cultural-sites-container">
                                    <div class="cultural-site-item mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" 
                                                       name="cultural_sites[0][name]" placeholder="Название">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" 
                                                       name="cultural_sites[0][description]" placeholder="Описание">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" 
                                                       name="cultural_sites[0][photo_url]" placeholder="URL фото">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="addCulturalSiteInput()">
                                    <i class="bi bi-plus"></i> Добавить место
                                </button>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Создать путешествие</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=YOUR_API_KEY"></script>
    <script src="/assets/js/main.js"></script>
    <script>
        let photoCount = 1;
        let culturalSiteCount = 1;

        function addPhotoInput() {
            const container = document.getElementById('photo-container');
            const newItem = document.createElement('div');
            newItem.className = 'photo-item mb-3';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <input type="file" class="form-control image-upload" 
                               name="photos[]" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" 
                               name="photo_descriptions[]" placeholder="Описание">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="radio" class="form-check-input" 
                                   name="main_photo" value="${photoCount}">
                            <label class="form-check-label">Главное фото</label>
                        </div>
                    </div>
                </div>
                <img class="image-preview mt-2" style="display: none; max-width: 200px;">
            `;
            container.appendChild(newItem);
            photoCount++;

            // Reinitialize image preview for new input
            const newInput = newItem.querySelector('.image-upload');
            const newPreview = newItem.querySelector('.image-preview');
            newInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    newPreview.src = e.target.result;
                    newPreview.style.display = 'block';
                }
                if (file) {
                    reader.readAsDataURL(file);
                }
            });
        }

        function addCulturalSiteInput() {
            const container = document.getElementById('cultural-sites-container');
            const newItem = document.createElement('div');
            newItem.className = 'cultural-site-item mb-3';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" 
                               name="cultural_sites[${culturalSiteCount}][name]" placeholder="Название">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" 
                               name="cultural_sites[${culturalSiteCount}][description]" placeholder="Описание">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" 
                               name="cultural_sites[${culturalSiteCount}][photo_url]" placeholder="URL фото">
                    </div>
                </div>
            `;
            container.appendChild(newItem);
            culturalSiteCount++;
        }
    </script>
</body>
</html>
