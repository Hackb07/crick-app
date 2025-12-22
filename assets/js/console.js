// Console page JavaScript - Wizard Flow with Auto-Progression
let wizardState = {
    team1Complete: false,
    team2Complete: false,
    tossComplete: false
};

function showTeam(team) {
    document.getElementById('view-team1').style.display = team === 'team1' ? 'block' : 'none';
    document.getElementById('view-team2').style.display = team === 'team2' ? 'block' : 'none';

    document.getElementById('tab-team1').classList.toggle('active', team === 'team1');
    document.getElementById('tab-team2').classList.toggle('active', team === 'team2');
}

function toggleCheck(el) {
    if (el.classList.contains('disabled')) return;

    const checkbox = el.querySelector('input[type="checkbox"]');
    const checkIcon = el.querySelector('.player-check');

    checkbox.checked = !checkbox.checked;
    el.classList.toggle('selected');
    checkIcon.innerHTML = checkbox.checked ? '✓' : '';
}

function updateTossUI(radio) {
    const name = radio.name;
    const radios = document.querySelectorAll(`input[name="${name}"]`);
    radios.forEach(r => {
        r.closest('.toss-card').classList.remove('active');
    });

    if (radio.checked) {
        radio.closest('.toss-card').classList.add('active');
    }

    checkTossComplete();
}

// Auto-advance after Team 1 squad saved
function onTeam1Saved() {
    wizardState.team1Complete = true;
    showNotification('✅ Team 1 squad saved! Now select Team 2 players.', 'success');

    setTimeout(() => {
        showTeam('team2');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 1500);
}

// Auto-advance after Team 2 squad saved
function onTeam2Saved() {
    wizardState.team2Complete = true;
    showNotification('✅ Team 2 squad saved! Now record the toss.', 'success');

    setTimeout(() => {
        const tossSection = document.querySelector('.toss-section');
        if (tossSection) {
            tossSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 1500);
}

// Check if toss is complete
function checkTossComplete() {
    const tossWinner = document.querySelector('input[name="toss_winner_id"]:checked');
    const tossDecision = document.querySelector('input[name="toss_decision"]:checked');

    if (tossWinner && tossDecision) {
        wizardState.tossComplete = true;
        showNotification('✅ Toss recorded! You can now start the match.', 'success');
    }
}

// Start match and open scorer in one click
function startMatchAndOpenScorer(form, matchId) {
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;

    btn.innerHTML = '⏳ Starting match...';
    btn.disabled = true;

    // Submit form via fetch
    fetch(form.action || window.location.href, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin'
    })
        .then(response => {
            if (response.ok) {
                btn.innerHTML = '✅ Match started! Opening scorer...';
                setTimeout(() => {
                    window.location.href = `scorer.php?id=${matchId}`;
                }, 1000);
            } else {
                throw new Error('Failed to start match');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Error starting match. Please try again.');
        });

    return false;
}

// Show notification
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.wizard-notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = 'wizard-notification';
    notification.style.cssText = `
        position: fixed;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#10b981' : '#3b82f6'};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        font-weight: 600;
        animation: slideDown 0.3s ease;
        max-width: 90%;
        text-align: center;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from { transform: translateX(-50%) translateY(-20px); opacity: 0; }
        to { transform: translateX(-50%) translateY(0); opacity: 1; }
    }
    @keyframes slideUp {
        from { transform: translateX(-50%) translateY(0); opacity: 1; }
        to { transform: translateX(-50%) translateY(-20px); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Detect successful form submissions and trigger auto-advance
document.addEventListener('DOMContentLoaded', function () {
    // Check URL for success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const teamId = urlParams.get('team_id');

    if (success) {
        // Determine which team was just saved
        const match = document.querySelector('[data-match-team1-id]');
        if (match && teamId) {
            const team1Id = match.dataset.matchTeam1Id;
            const team2Id = match.dataset.matchTeam2Id;

            if (teamId === team1Id) {
                onTeam1Saved();
            } else if (teamId === team2Id) {
                onTeam2Saved();
            }
        }
    }
});
