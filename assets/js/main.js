// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize image upload preview
    const imageInput = document.querySelector('.image-upload');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const preview = document.querySelector('.image-preview');
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        });
    }

    // Initialize map if element exists
    const mapElement = document.getElementById('travel-map');
    if (mapElement && typeof ymaps !== 'undefined') {
        ymaps.ready(function() {
            initMap(mapElement);
        });
    }

    // Initialize rating system
    const ratingInputs = document.querySelectorAll('.rating-input');
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const stars = this.parentElement.querySelectorAll('.rating-star');
            const value = this.value;
            
            stars.forEach((star, index) => {
                if (index < value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        });
    });

    // Gallery image modal
    const galleryImages = document.querySelectorAll('.gallery-image');
    galleryImages.forEach(img => {
        img.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImg = document.querySelector('#imageModal .modal-img');
            modalImg.src = this.src;
            modal.show();
        });
    });

    // Cost calculator
    const costInputs = document.querySelectorAll('.cost-input');
    if (costInputs.length > 0) {
        costInputs.forEach(input => {
            input.addEventListener('input', calculateTotalCost);
        });
    }
});

// Map initialization function
function initMap(element) {
    const lat = parseFloat(element.dataset.lat) || 55.7558;
    const lng = parseFloat(element.dataset.lng) || 37.6173;
    
    const map = new ymaps.Map(element, {
        center: [lat, lng],
        zoom: 12,
        controls: ['zoomControl', 'searchControl']
    });

    // Add marker if coordinates are provided
    if (element.dataset.lat && element.dataset.lng) {
        const placemark = new ymaps.Placemark([lat, lng], {
            balloonContent: element.dataset.title || ''
        });
        map.geoObjects.add(placemark);
    }

    // Add click listener for adding new markers if map is editable
    if (element.dataset.editable === 'true') {
        let currentPlacemark = null;

        map.events.add('click', function(e) {
            const coords = e.get('coords');
            
            // Update form inputs
            document.getElementById('latitude').value = coords[0].toFixed(6);
            document.getElementById('longitude').value = coords[1].toFixed(6);
            
            // Remove previous marker
            if (currentPlacemark) {
                map.geoObjects.remove(currentPlacemark);
            }
            
            // Add new marker
            currentPlacemark = new ymaps.Placemark(coords);
            map.geoObjects.add(currentPlacemark);
        });
    }
}

// Cost calculator function
function calculateTotalCost() {
    const costs = Array.from(document.querySelectorAll('.cost-input'))
        .map(input => parseFloat(input.value) || 0);
    
    const total = costs.reduce((sum, cost) => sum + cost, 0);
    document.getElementById('total-cost').textContent = total.toFixed(2);
}

// Form validation
function validateTravelForm() {
    const form = document.getElementById('travel-form');
    if (!form) return true;

    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// AJAX functions for dynamic content loading
async function loadMoreTravels(page) {
    try {
        const response = await fetch(`/api/travels.php?page=${page}`);
        const data = await response.json();
        
        const container = document.querySelector('.travels-container');
        container.insertAdjacentHTML('beforeend', data.html);
        
        if (!data.hasMore) {
            document.getElementById('load-more').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading more travels:', error);
    }
}

// Notifications
function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast show position-fixed bottom-0 end-0 m-3 bg-${type}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">Уведомление</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body text-white">
            ${message}
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
