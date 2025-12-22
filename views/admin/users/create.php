<div class="content-container">
    
    <?php if ($error): ?>
        <div class="card mb-4" style="background: #fef2f2; border-color: #fee2e2;">
            <div class="card-body" style="color: var(--danger);">
                <?= e($error) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div style="margin-bottom: 16px;">
                    <label for="username" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" required value="<?= e(getPost('username', '')) ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="email" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= e(getPost('email', '')) ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="password" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Minimum 6 characters</small>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="full_name" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?= e(getPost('full_name', '')) ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="role" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Role *</label>
                    <select id="role" name="role" class="form-select" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                        <option value="user" <?= getPost('role', '') === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="scorer" <?= getPost('role', '') === 'scorer' ? 'selected' : '' ?>>Scorer</option>
                        <option value="admin" <?= getPost('role', '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">Status</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="is_active" name="is_active" <?= ($_SERVER['REQUEST_METHOD'] !== 'POST' || getPost('is_active')) ? 'checked' : '' ?> style="width: 20px; height: 20px;">
                        <label for="is_active" style="margin: 0;">Active</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create User</button>
            </form>
        </div>
    </div>

</div>
