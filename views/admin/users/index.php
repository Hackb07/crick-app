<div class="content-container">
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label for="role" style="display: block; margin-bottom: 4px; font-size: 0.85rem; color: var(--text-muted);">Role</label>
                    <select id="role" name="role" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-body);" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="scorer" <?= ($role ?? '') === 'scorer' ? 'selected' : '' ?>>Scorer</option>
                        <option value="user" <?= ($role ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                    </select>
                </div>
                <div>
                    <label for="is_active" style="display: block; margin-bottom: 4px; font-size: 0.85rem; color: var(--text-muted);">Status</label>
                    <select id="is_active" name="is_active" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-body);" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="1" <?= ($isActive ?? null) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($isActive ?? null) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List -->
    <?php if (empty($users)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
            <h3 style="margin-bottom: 8px;">No Users Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Create your first user to get started.</p>
            <a href="<?= adminUrl('users/create.php') ?>" class="btn btn-primary">Create User</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="list-group">
                <?php foreach ($users as $user): ?>
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title">
                                <?= e($user['username']) ?>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span style="font-size: 0.75rem; background: var(--primary); color: white; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">Admin</span>
                                <?php endif; ?>
                            </div>
                            <div class="list-item-subtitle">
                                <?= e($user['email']) ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span class="badge <?= $user['is_active'] ? 'badge-live' : 'badge-scheduled' ?>" style="font-size: 0.7rem;">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                            <a href="<?= adminUrl('users/view.php?id=' . $user['user_id']) ?>" class="btn-icon" aria-label="View">
                                👁️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
