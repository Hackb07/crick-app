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
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Player Name *</label>
                    <input type="text" id="name" name="name" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" value="<?= e($player['name']) ?>" required autofocus>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="date_of_birth" style="display: block; margin-bottom: 8px; font-weight: 500;">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" value="<?= e($player['date_of_birth'] ?? '') ?>">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="batting_hand" style="display: block; margin-bottom: 8px; font-weight: 500;">Batting Hand</label>
                    <select id="batting_hand" name="batting_hand" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                        <option value="">Select Batting Hand</option>
                        <option value="Right" <?= ($player['batting_hand'] ?? '') === 'Right' ? 'selected' : '' ?>>Right</option>
                        <option value="Left" <?= ($player['batting_hand'] ?? '') === 'Left' ? 'selected' : '' ?>>Left</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="bowling_style" style="display: block; margin-bottom: 8px; font-weight: 500;">Bowling Style</label>
                    <select id="bowling_style" name="bowling_style" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                        <option value="">Select Bowling Style</option>
                        <option value="Right-arm Fast" <?= ($player['bowling_style'] ?? '') === 'Right-arm Fast' ? 'selected' : '' ?>>Right-arm Fast</option>
                        <option value="Right-arm Medium" <?= ($player['bowling_style'] ?? '') === 'Right-arm Medium' ? 'selected' : '' ?>>Right-arm Medium</option>
                        <option value="Right-arm Spin" <?= ($player['bowling_style'] ?? '') === 'Right-arm Spin' ? 'selected' : '' ?>>Right-arm Spin</option>
                        <option value="Left-arm Fast" <?= ($player['bowling_style'] ?? '') === 'Left-arm Fast' ? 'selected' : '' ?>>Left-arm Fast</option>
                        <option value="Left-arm Medium" <?= ($player['bowling_style'] ?? '') === 'Left-arm Medium' ? 'selected' : '' ?>>Left-arm Medium</option>
                        <option value="Left-arm Spin" <?= ($player['bowling_style'] ?? '') === 'Left-arm Spin' ? 'selected' : '' ?>>Left-arm Spin</option>
                        <option value="N/A" <?= ($player['bowling_style'] ?? '') === 'N/A' ? 'selected' : '' ?>>N/A</option>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="profile_image" style="display: block; margin-bottom: 8px; font-weight: 500;">Profile Image URL</label>
                    <input type="url" id="profile_image" name="profile_image" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" value="<?= e($player['profile_image'] ?? '') ?>" placeholder="https://example.com/image.jpg">
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Update Player</button>
            </form>
        </div>
    </div>

</div>
