<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$pageTitle = 'Eco-Radar';
$results = [];
$error = '';
$searchPerformed = false;

// Handle AJAX or POST Search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latitude'], $_POST['longitude'])) {
    $userLat = (float)$_POST['latitude'];
    $userLon = (float)$_POST['longitude'];
    $radius = 50; // Search radius in KM (increased for testing)

    try {
        // Haversine Formula for Distance Calculation (in KM)
        $sql = "SELECT id, location_description, description, photo_path, latitude, longitude, created_at, status,
                ( 6371 * acos( cos( radians(:lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(:lon) ) + sin( radians(:lat) ) * sin( radians( latitude ) ) ) ) AS distance 
                FROM trash_reports 
                HAVING distance < :radius 
                ORDER BY distance ASC 
                LIMIT 20";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':lat' => $userLat,
            ':lon' => $userLon,
            ':radius' => $radius
        ]);
        $results = $stmt->fetchAll();
        $searchPerformed = true;

        // If AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            
            // Format for JSON
            foreach ($results as &$row) {
                $row['distance'] = round($row['distance'], 2);
                $row['time_ago'] = time_elapsed_string($row['created_at']);
                $row['photo_path'] = $row['photo_path'] ? '../../uploads/reports/' . $row['photo_path'] : null;
            }
            echo json_encode(['success' => true, 'data' => $results]);
            exit;
        }

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
             echo json_encode(['success' => false, 'error' => $error]);
             exit;
        }
    }
}

include '../../includes/header.php';

// Helper for time ago
function time_elapsed_string($datetime, $full = false) {
    if (empty($datetime)) return 'Unknown';
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day',
        'h' => 'hour', 'i' => 'minute', 's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<div class="dashboard-header bg-dark text-white p-5 mb-0" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://source.unsplash.com/1600x900/?radar,map') no-repeat center center/cover;">
    <div class="container text-center">
        <h1 class="display-4 fw-bold"><i class="bi-broadcast me-3"></i>Eco-Radar</h1>
        <p class="lead">Scan your surroundings for reported ecological hazards.</p>
        <button onclick="startScan()" class="btn btn-success btn-lg px-5 mt-3 rounded-pill shadow-lg hover-scale">
            <i class="bi-crosshair me-2"></i> Activate Scanner
        </button>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <!-- Status / Spinner -->
        <div id="scanStatus" class="text-center mb-5" style="display: none;">
            <div class="spinner-grow text-success" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Scanning...</span>
            </div>
            <h4 class="mt-3 text-muted">Scanning 50km radius...</h4>
            <p id="coordsDisplay" class="small text-muted"></p>
        </div>

        <!-- Error Alert -->
        <div id="errorAlert" class="alert alert-danger shadow-sm" style="display: none;"></div>

        <!-- Results Grid -->
        <div id="resultsGrid" class="row g-4">
            <!-- Results will be injected here -->
        </div>

        <!-- Default State -->
        <?php if (!$searchPerformed): ?>
        <div id="defaultState" class="text-center py-5">
            <i class="bi-geo-alt display-1 text-muted opacity-25"></i>
            <p class="mt-3 text-muted">Click "Activate Scanner" to use your current location.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.hover-scale { transition: transform 0.2s; }
.hover-scale:hover { transform: scale(1.05); }
.distance-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: #4caf50;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
    backdrop-filter: blur(5px);
}
</style>

<script>
function startScan() {
    const status = document.getElementById('scanStatus');
    const defaultState = document.getElementById('defaultState');
    const errorAlert = document.getElementById('errorAlert');
    const resultsGrid = document.getElementById('resultsGrid');
    
    // Reset UI
    if (defaultState) defaultState.style.display = 'none';
    errorAlert.style.display = 'none';
    resultsGrid.innerHTML = '';
    status.style.display = 'block';

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(performSearch, handleError, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    } else {
        showError("Geolocation is not supported by this browser.");
    }
}

function handleError(error) {
    let msg = "An unknown error occurred.";
    switch(error.code) {
        case error.PERMISSION_DENIED: msg = "User denied the request for Geolocation."; break;
        case error.POSITION_UNAVAILABLE: msg = "Location information is unavailable."; break;
        case error.TIMEOUT: msg = "The request to get user location timed out."; break;
    }
    showError(msg);
}

function showError(msg) {
    document.getElementById('scanStatus').style.display = 'none';
    const alert = document.getElementById('errorAlert');
    alert.textContent = msg;
    alert.style.display = 'block';
}

function performSearch(position) {
    const lat = position.coords.latitude;
    const lon = position.coords.longitude;
    
    document.getElementById('coordsDisplay').textContent = `Loc: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;

    const formData = new FormData();
    formData.append('latitude', lat);
    formData.append('longitude', lon);

    fetch('radar.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('scanStatus').style.display = 'none';
        
        if (data.success) {
            if (data.data.length === 0) {
                resultsGrid.innerHTML = '<div class="col-12 text-center text-muted"><h4>No reports found nearby (50km).</h4><p>You can be the first to report something!</p></div>';
            } else {
                renderResults(data.data);
            }
        } else {
            showError(data.error || "Search failed.");
        }
    })
    .catch(err => {
        console.error(err);
        showError("Network error occurred.");
    });
}

function renderResults(reports) {
    const grid = document.getElementById('resultsGrid');
    let html = '';
    
    reports.forEach(report => {
        const imageHtml = report.photo_path 
            ? `<img src="${report.photo_path}" class="card-img-top" alt="Trash Photo" style="height: 200px; object-fit: cover;">`
            : `<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;"><i class="bi-camera-slash fs-1"></i></div>`;

        const badgeClass = report.status === 'resolved' ? 'success' : (report.status === 'rejected' ? 'danger' : 'warning');
        
        html += `
        <div class="col-md-4 col-sm-6 fade-in">
            <div class="card h-100 shadow-sm border-0 hover-scale">
                ${imageHtml}
                <div class="distance-badge">
                    <i class="bi-cursor-fill me-1"></i>${report.distance} km
                </div>
                <div class="card-body">
                    <h5 class="card-title text-truncate">${report.location_description}</h5>
                    <p class="card-text text-muted small table-responsive mb-2" style="max-height: 200px; overflow-y: auto;">
                        ${report.description}
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="badge bg-${badgeClass} text-uppercase">${report.status}</span>
                        <small class="text-muted"><i class="bi-clock me-1"></i>${report.time_ago}</small>
                    </div>
                </div>
            </div>
        </div>
        `;
    });
    
    grid.innerHTML = html;
}
</script>

<?php include '../../includes/footer.php'; ?>
