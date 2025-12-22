<div class="content-container">
    
    <div class="card mb-4">
        <div class="card-body text-center">
            <div style="width: 80px; height: 80px; background: var(--bg-body); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                👤
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 4px;"><?= e($user['full_name'] ?: $user['username']) ?></h1>
            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 8px;">
                <?= e($user['email']) ?>
            </div>
            <div>
                <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'scorer' ? 'warning' : 'secondary') ?>">
                    <?= ucfirst($user['role']) ?>
                </span>
                <span class="badge <?= $user['is_active'] ? 'badge-live' : 'badge-scheduled' ?>" style="margin-left: 8px;">
                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Details</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= e($user['username']) ?></div>
                    <div class="stat-label">Username</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                    <div class="stat-label">Joined</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Actions</h3>
            <div style="display: flex; gap: 12px;">
                <a href="<?= adminUrl('users/edit.php?id=' . $user['user_id']) ?>" class="btn btn-primary" style="flex: 1;">Edit</a>
                <?php if ($user['user_id'] != getUserId()): ?>
                    <a href="<?= adminUrl('users/delete.php?id=' . $user['user_id']) ?>" class="btn btn-secondary" onclick="return confirm('Delete user?');" style="flex: 1; background: #fee2e2; color: var(--danger); border-color: #fee2e2;">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
