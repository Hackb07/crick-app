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
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Series Name *</label>
                    <input type="text" id="name" name="name" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" required autofocus>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="start_date" style="display: block; margin-bottom: 8px; font-weight: 500;">Start Date</label>
                    <input type="date" id="start_date" name="start_date" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="end_date" style="display: block; margin-bottom: 8px; font-weight: 500;">End Date</label>
                    <input type="date" id="end_date" name="end_date" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500;">Description</label>
                    <textarea id="description" name="description" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);" rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Series</button>
            </form>
        </div>
    </div>

</div>
