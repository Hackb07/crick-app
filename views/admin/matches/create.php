<div class="content-container">
    
    <?php if (!empty($error)): ?>
        <div class="card mb-4" style="background: #fef2f2; border-color: #fee2e2;">
            <div class="card-body" style="color: var(--danger);">
                <?= e($error) ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" id="matchForm">
        <?= csrfInput() ?>
        <!-- Team 1 -->
        <div class="card mb-4">
            <div class="card-header">Select Team 1</div>
            <div class="card-body">
                <div class="team-grid" id="team1-grid">
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card" data-team-id="<?= $team['team_id'] ?>" data-team-name="<?= e($team['name']) ?>" onclick="selectTeam(1, <?= $team['team_id'] ?>, '<?= e($team['name']) ?>')">
                            <?php if (!empty($team['logo'])): ?>
                                <img src="<?= e($team['logo']) ?>" alt="<?= e($team['name']) ?>" class="team-logo">
                            <?php else: ?>
                                <div class="team-logo-placeholder"><?= strtoupper(substr($team['name'], 0, 2)) ?></div>
                            <?php endif; ?>
                            <div class="team-name"><?= e($team['name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="team1_id" name="team1_id" required>
                <div id="team1-error" style="color: var(--danger); font-size: 12px; margin-top: 8px; display: none;"></div>
            </div>
        </div>

        <!-- Team 2 -->
        <div class="card mb-4">
            <div class="card-header">Select Team 2</div>
            <div class="card-body">
                <div class="team-grid" id="team2-grid">
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card" data-team-id="<?= $team['team_id'] ?>" data-team-name="<?= e($team['name']) ?>" onclick="selectTeam(2, <?= $team['team_id'] ?>, '<?= e($team['name']) ?>')">
                            <?php if (!empty($team['logo'])): ?>
                                <img src="<?= e($team['logo']) ?>" alt="<?= e($team['name']) ?>" class="team-logo">
                            <?php else: ?>
                                <div class="team-logo-placeholder"><?= strtoupper(substr($team['name'], 0, 2)) ?></div>
                            <?php endif; ?>
                            <div class="team-name"><?= e($team['name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="team2_id" name="team2_id" required>
                <div id="team2-error" style="color: var(--danger); font-size: 12px; margin-top: 8px; display: none;"></div>
            </div>
        </div>

        <!-- Match Details & Settings -->
        <div class="card mb-4">
            <div class="card-header">Match Settings</div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div style="grid-column: span 2;">
                        <label for="series_id" class="form-label">Series</label>
                        <select id="series_id" name="series_id" class="form-select">
                            <option value="">Select Series (Optional)</option>
                            <?php foreach ($series as $s): ?>
                                <option value="<?= $s['series_id'] ?>"><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="grid-column: span 2;">
                        <label for="venue" class="form-label">Venue</label>
                        <input type="text" id="venue" name="venue" class="form-control" placeholder="Enter venue">
                    </div>

                    <div>
                        <label for="match_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" id="match_date" name="match_date" class="form-control">
                    </div>

                    <div>
                        <label for="overs_per_innings" class="form-label">Overs per Innings</label>
                        <input type="number" id="overs_per_innings" name="overs_per_innings" class="form-control" value="20" min="1" max="50">
                    </div>

                    <div>
                        <label for="match_type" class="form-label">Match Type</label>
                        <select id="match_type" name="match_type" class="form-select">
                            <option value="limited_overs">Limited Overs</option>
                            <option value="test">Test Match</option>
                            <option value="box">Box Cricket</option>
                        </select>
                    </div>

                    <div>
                        <label for="ball_type" class="form-label">Ball Type</label>
                        <select id="ball_type" name="ball_type" class="form-select">
                            <option value="leather">Leather</option>
                            <option value="tennis">Tennis</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="pitch_type" class="form-label">Pitch Type</label>
                        <select id="pitch_type" name="pitch_type" class="form-select">
                            <option value="turf">Turf</option>
                            <option value="cement">Cement</option>
                            <option value="matting">Matting</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Match Officials -->
        <div class="card mb-4">
            <div class="card-header">Match Officials</div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div>
                        <label for="umpire1_name" class="form-label">Umpire 1</label>
                        <input type="text" id="umpire1_name" name="umpire1_name" class="form-control" placeholder="Main Umpire">
                    </div>
                    <div>
                        <label for="umpire2_name" class="form-label">Umpire 2</label>
                        <input type="text" id="umpire2_name" name="umpire2_name" class="form-control" placeholder="Leg Umpire">
                    </div>
                    <div>
                        <label for="scorer_name" class="form-label">Scorer Name</label>
                        <input type="text" id="scorer_name" name="scorer_name" class="form-control" placeholder="Scorer Name">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Preview & Action -->
        <div id="selected-teams-preview" style="display: none;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 12px; color: var(--text-muted);">Team 1</div>
                    <div style="font-weight: 600;" id="team1-preview-name"></div>
                </div>
                <div style="font-weight: 700; color: var(--text-muted); padding: 0 12px;">VS</div>
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 12px; color: var(--text-muted);">Team 2</div>
                    <div style="font-weight: 600;" id="team2-preview-name"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Match</button>
        </div>

    </form>
</div>

<style>
    /* Custom styles for Team Selection */
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 12px;
    }
    
    .team-card {
        background: var(--bg-body);
        border: 2px solid var(--border);
        border-radius: var(--radius-md);
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        min-height: 110px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .team-card.selected {
        border-color: var(--primary);
        background: var(--primary-light);
        box-shadow: 0 0 0 2px var(--primary-light);
    }
    
    .team-card.disabled {
        opacity: 0.4;
        pointer-events: none;
        filter: grayscale(1);
    }
    
    .team-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
        margin-bottom: 8px;
    }
    
    .team-logo-placeholder {
        width: 40px;
        height: 40px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .team-name {
        font-weight: 600;
        font-size: 12px;
        line-height: 1.2;
        color: var(--text-main);
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Sticky Preview Bar */
    #selected-teams-preview {
        position: sticky;
        bottom: 0;
        background: var(--bg-card);
        border-top: 1px solid var(--border);
        padding: 16px;
        margin: 0 -16px -16px -16px; /* Negative margin to stretch full width */
        z-index: 100;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
        padding-bottom: calc(16px + var(--safe-bottom));
    }

    .form-control, .form-select {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--bg-body);
        font-size: 16px; /* Prevent zoom on iOS */
        color: var(--text-main);
    }
    
    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 14px;
        color: var(--text-muted);
    }
</style>

<script>
    // Team Selection Logic
    let selectedTeam1 = null;
    let selectedTeam2 = null;
    
    function selectTeam(teamNumber, teamId, teamName) {
        const team1Input = document.getElementById('team1_id');
        const team2Input = document.getElementById('team2_id');
        const team1Error = document.getElementById('team1-error');
        const team2Error = document.getElementById('team2-error');
        
        if (teamNumber === 1) {
            // Deselect previous Team 1
            if (selectedTeam1) {
                const prevCard = document.querySelector(`#team1-grid .team-card[data-team-id="${selectedTeam1}"]`);
                if(prevCard) prevCard.classList.remove('selected');
            }
            
            // Select new Team 1
            selectedTeam1 = teamId;
            team1Input.value = teamId;
            const newCard = document.querySelector(`#team1-grid .team-card[data-team-id="${teamId}"]`);
            if(newCard) newCard.classList.add('selected');
            team1Error.style.display = 'none';
            
            updateTeam2Availability();
            updatePreview();
        } else {
            // Deselect previous Team 2
            if (selectedTeam2) {
                const prevCard = document.querySelector(`#team2-grid .team-card[data-team-id="${selectedTeam2}"]`);
                if(prevCard) prevCard.classList.remove('selected');
            }
            
            // Select new Team 2
            selectedTeam2 = teamId;
            team2Input.value = teamId;
            const newCard = document.querySelector(`#team2-grid .team-card[data-team-id="${teamId}"]`);
            if(newCard) newCard.classList.add('selected');
            team2Error.style.display = 'none';
            
            updateTeam1Availability();
            updatePreview();
        }
    }
    
    function updateTeam1Availability() {
        const team1Cards = document.querySelectorAll('#team1-grid .team-card');
        team1Cards.forEach(card => {
            const teamId = parseInt(card.dataset.teamId);
            if (selectedTeam2 && teamId === selectedTeam2) {
                card.classList.add('disabled');
            } else {
                card.classList.remove('disabled');
            }
        });
    }
    
    function updateTeam2Availability() {
        const team2Cards = document.querySelectorAll('#team2-grid .team-card');
        team2Cards.forEach(card => {
            const teamId = parseInt(card.dataset.teamId);
            if (selectedTeam1 && teamId === selectedTeam1) {
                card.classList.add('disabled');
            } else {
                card.classList.remove('disabled');
            }
        });
    }
    
    function updatePreview() {
        const preview = document.getElementById('selected-teams-preview');
        const team1PreviewName = document.getElementById('team1-preview-name');
        const team2PreviewName = document.getElementById('team2-preview-name');
        
        if (selectedTeam1 && selectedTeam2) {
            const team1Card = document.querySelector(`#team1-grid .team-card[data-team-id="${selectedTeam1}"]`);
            const team2Card = document.querySelector(`#team2-grid .team-card[data-team-id="${selectedTeam2}"]`);
            
            if(team1Card) team1PreviewName.textContent = team1Card.dataset.teamName;
            if(team2Card) team2PreviewName.textContent = team2Card.dataset.teamName;
            
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }
    
    // Form validation
    document.getElementById('matchForm').addEventListener('submit', function(e) {
        const team1Id = document.getElementById('team1_id').value;
        const team2Id = document.getElementById('team2_id').value;
        const team1Error = document.getElementById('team1-error');
        const team2Error = document.getElementById('team2-error');
        let isValid = true;
        
        if (!team1Id) {
            team1Error.textContent = 'Please select Team 1';
            team1Error.style.display = 'block';
            isValid = false;
        }
        
        if (!team2Id) {
            team2Error.textContent = 'Please select Team 2';
            team2Error.style.display = 'block';
            isValid = false;
        }
        
        if (team1Id && team2Id && team1Id === team2Id) {
            team2Error.textContent = 'Team 2 must be different from Team 1';
            team2Error.style.display = 'block';
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            const firstError = document.querySelector('.error[style*="display: block"]');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
