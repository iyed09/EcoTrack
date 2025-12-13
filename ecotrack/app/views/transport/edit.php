<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-car-front me-2"></i>Edit Transport Entry</h2>
        <p>Update your travel record</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header transport">
                        <h5 class="mb-0">Edit Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Transport Type *</label>
                                <select name="transport_id" class="form-control" required>
                                    <?php foreach ($transports as $transport): ?>
                                    <option value="<?php echo $transport['id']; ?>" <?php echo $transport['id'] == $entry['transport_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($transport['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Distance (km) *</label>
                                <input type="number" name="distance_km" class="form-control" step="0.1" min="0" required value="<?php echo $entry['distance_km']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo $entry['date']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($entry['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Update Entry</button>
                                <a href="<?php echo URL_ROOT; ?>/transport" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
