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
                    <input type="text" id="name" name="name" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" required autofocus>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="date_of_birth" style="display: block; margin-bottom: 8px; font-weight: 500;">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="batting_hand" style="display: block; margin-bottom: 8px; font-weight: 500;">Batting Hand</label>
                    <select id="batting_hand" name="batting_hand" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                        <option value="">Select Batting Hand</option>
                        <option value="Right">Right</option>
                        <option value="Left">Left</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="bowling_style" style="display: block; margin-bottom: 8px; font-weight: 500;">Bowling Style</label>
                    <select id="bowling_style" name="bowling_style" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                        <option value="">Select Bowling Style</option>
                        <option value="Right-arm Fast">Right-arm Fast</option>
                        <option value="Right-arm Medium">Right-arm Medium</option>
                        <option value="Right-arm Spin">Right-arm Spin</option>
                        <option value="Left-arm Fast">Left-arm Fast</option>
                        <option value="Left-arm Medium">Left-arm Medium</option>
                        <option value="Left-arm Spin">Left-arm Spin</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="profile_image" style="display: block; margin-bottom: 8px; font-weight: 500;">Profile Image URL</label>
                    <input type="url" id="profile_image" name="profile_image" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" placeholder="https://example.com/image.jpg">
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Player</button>
            </form>
        </div>
    </div>

</div>
