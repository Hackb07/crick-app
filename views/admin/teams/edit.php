<div class="content-container">
    
    <?php if (!empty($error)): ?>
        <div class="card mb-4" style="background: #fef2f2; border-color: #fee2e2;">
            <div class="card-body" style="color: var(--danger);">
                <?= e($error) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <?= csrfInput() ?>
                <div style="margin-bottom: 16px;">
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Team Name *</label>
                    <input type="text" id="name" name="name" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" value="<?= e($team['name']) ?>" required autofocus>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="short_name" style="display: block; margin-bottom: 8px; font-weight: 500;">Short Name</label>
                    <input type="text" id="short_name" name="short_name" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" value="<?= e($team['short_name'] ?? '') ?>" placeholder="e.g., CSK, MI">
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="logo" style="display: block; margin-bottom: 8px; font-weight: 500;">Logo URL</label>
                    <input type="url" id="logo" name="logo" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" value="<?= e($team['logo'] ?? '') ?>" placeholder="https://example.com/logo.png">
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Update Team</button>
            </form>
        </div>
    </div>
</div>
