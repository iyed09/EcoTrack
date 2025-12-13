<?php
require_once '../../includes/config.php';
require_once '../../includes/PointsManager.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';
$pointsManager = new PointsManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = sanitize($_POST['location_description'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;

    if (empty($location) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        $photoPath = null;
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $maxSize = 5 * 1024 * 1024;
            
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowedExtensions)) {
                $error = 'Only JPG, PNG and GIF images are allowed.';
            } elseif ($_FILES['photo']['size'] > $maxSize) {
                $error = 'Image size must be less than 5MB.';
            } else {
                $imageInfo = @getimagesize($_FILES['photo']['tmp_name']);
                if ($imageInfo === false) {
                    $error = 'Invalid image file.';
                } else {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($imageInfo['mime'], $allowedMimes)) {
                        $error = 'Invalid image type.';
                    } else {
                        $safeExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                        $photoPath = uniqid('report_') . '.' . $safeExt[$imageInfo['mime']];
                        $uploadDir = '../../uploads/reports/';
                        
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoPath)) {
                            $error = 'Failed to upload image.';
                            $photoPath = null;
                        }
                    }
                }
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, photo_path) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$userId, $location, $latitude, $longitude, $description, $photoPath])) {
                $reportId = $pdo->lastInsertId();
                $points = PointsManager::getPointsForAction('trash_report');
                $pointsManager->awardPoints($userId, $points, 'trash_report', 'Reported improper trash disposal', $reportId, 'trash_report');
                $_SESSION['points_earned'] = $points;
                $_SESSION['points_message'] = 'Trash report submitted!';
                redirect('index.php');
            } else {
                $error = 'Failed to submit report. Please try again.';
            }
        }
    }
}

$pageTitle = 'Report Trash';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-flag me-2"></i>Report Improper Trash Disposal</h2>
        <p>Help keep our community clean</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="module-card">
                    <div class="module-card-header report">
                        <h5 class="mb-0">Submit a New Report</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Location Description *</label>
                                <input type="text" name="location_description" class="form-control" required placeholder="e.g., Near the park entrance on Main Street">
                                <small class="text-muted">Describe where you found the improper trash disposal</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude (optional)</label>
                                    <input type="number" name="latitude" id="latitude" class="form-control" step="any" placeholder="e.g., 36.8065">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude (optional)</label>
                                    <input type="number" name="longitude" id="longitude" class="form-control" step="any" placeholder="e.g., 10.1815">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="getLocation()">
                                    <i class="bi-geo-alt me-1"></i>Use My Location
                                </button>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" id="description" class="form-control" rows="4" required placeholder="Describe what you observed (type of trash, severity, etc.)"></textarea>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-sm btn-info text-white" onclick="analyzeTrash()">
                                        <i class="bi-stars me-1"></i> Analyze with AI
                                    </button>
                                </div>
                            </div>

                            <!-- AI Analysis Result Container -->
                            <div id="aiResult" class="mb-4" style="display: none;">
                                <div class="card bg-light border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="spinner-border text-primary me-2" role="status" id="aiSpinner" style="display: none;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <h6 class="card-title mb-0 text-primary fw-bold" id="aiTitle">
                                                <i class="bi-robot me-2"></i>AI Analysis Result
                                            </h6>
                                        </div>
                                        
                                        <div id="aiContent" style="display: none;">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-white rounded border">
                                                        <small class="text-muted d-block mb-1">Detected Type</small>
                                                        <strong class="fs-5 text-dark" id="aiType"></strong>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-white rounded border">
                                                        <small class="text-muted d-block mb-1">Severity</small>
                                                        <span class="badge" id="aiSeverity"></span>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="p-3 bg-white rounded border">
                                                        <small class="text-muted d-block mb-1">Environmental Impact</small>
                                                        <p class="mb-0 text-secondary" id="aiDecomposition"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Photo (optional)</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                <small class="text-muted">Upload a photo of the trash (max 5MB, JPG/PNG/GIF)</small>
                                <img id="imagePreview" class="mt-2 rounded" style="display: none; max-width: 100%; max-height: 200px;">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Submit Report</button>
                                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
        }, function(error) {
            alert('Unable to get your location. Please enter manually.');
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

function analyzeTrash() {
    const description = document.getElementById('description').value;
    const resultContainer = document.getElementById('aiResult');
    const spinner = document.getElementById('aiSpinner');
    const content = document.getElementById('aiContent');

    if (description.length < 5) {
        alert('Please enter a longer description first.');
        return;
    }

    // Show processing state
    resultContainer.style.display = 'block';
    spinner.style.display = 'block';
    content.style.display = 'none';

    // Call API
    const formData = new FormData();
    formData.append('description', description);

    fetch('analyze_trash.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        spinner.style.display = 'none';
        content.style.display = 'block';
        
        if (data.success) {
            const ai = data.data;
            document.getElementById('aiType').innerHTML = '<i class="' + ai.icon + ' me-2"></i>' + ai.type;
            
            const badge = document.getElementById('aiSeverity');
            badge.textContent = ai.severity;
            badge.className = 'badge rounded-pill ' + 
                (ai.score > 70 ? 'bg-danger' : (ai.score > 40 ? 'bg-warning' : 'bg-success'));
            
            document.getElementById('aiDecomposition').textContent = 
                'Est. Decomposition: ' + ai.decomposition + '. ' + ai.message;
        }
    })
    .catch(error => {
        spinner.style.display = 'none';
        alert('AI Analysis failed. Please try again.');
        console.error(error);
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
