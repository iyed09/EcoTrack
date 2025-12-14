<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-trash me-2"></i>Add Waste Entry</h2>
        <p>Log your waste disposal</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header waste">
                        <h5 class="mb-0">New Waste Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Waste Type *</label>
                                <select name="waste_type_id" class="form-control" required>
                                    <option value="">Select waste type</option>
                                    <?php foreach ($wasteTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?> <?php echo $type['recyclable'] ? '(Recyclable)' : ''; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Weight (kg) *</label>
                                <input type="number" name="weight_kg" class="form-control" step="0.1" min="0" required placeholder="Enter weight">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="properly_disposed" class="form-check-input" id="properlyDisposed" checked>
                                <label class="form-check-label" for="properlyDisposed">Properly disposed/recycled</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Add Entry</button>
                                <a href="<?php echo URL_ROOT; ?>/waste" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
