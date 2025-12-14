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
                        <?php if (isset($error) && $error): ?>
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
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Photo (optional)</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                <small class="text-muted">Upload a photo of the trash (max 5MB, JPG/PNG/GIF)</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Submit Report</button>
                                <a href="<?php echo URL_ROOT; ?>/reports" class="btn btn-outline-secondary">Cancel</a>
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
</script>
