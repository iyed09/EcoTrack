<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-trash me-2"></i>Edit Waste Entry</h2>
        <p>Update your waste record</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header waste">
                        <h5 class="mb-0">Edit Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Waste Type *</label>
                                <select name="waste_type_id" class="form-control" required>
                                    <?php foreach ($wasteTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>" <?php echo $type['id'] == $entry['waste_type_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Weight (kg) *</label>
                                <input type="number" name="weight_kg" class="form-control" step="0.1" min="0" required value="<?php echo $entry['weight_kg']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo $entry['date']; ?>">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="properly_disposed" class="form-check-input" id="properlyDisposed" <?php echo $entry['properly_disposed'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="properlyDisposed">Properly disposed/recycled</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($entry['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Update Entry</button>
                                <a href="<?php echo URL_ROOT; ?>/waste" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
