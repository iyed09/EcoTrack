<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-lightning-charge me-2"></i>Add Energy Entry</h2>
        <p>Log your energy consumption</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header energy">
                        <h5 class="mb-0">New Energy Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Energy Source *</label>
                                <select name="source_id" class="form-control" required>
                                    <option value="">Select source</option>
                                    <?php foreach ($sources as $source): ?>
                                    <option value="<?php echo $source['id']; ?>"><?php echo htmlspecialchars($source['name']); ?> (<?php echo $source['unit']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Amount *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required placeholder="Enter amount">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Add Entry</button>
                                <a href="<?php echo URL_ROOT; ?>/energy" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
