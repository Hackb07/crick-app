function autoSelectPlayers(formId) {
    const form = document.getElementById(formId);
    const checkboxes = Array.from(form.querySelectorAll('input[name="player_ids[]"]'));
    checkboxes.forEach(cb => {
        cb.checked = false;
        cb.closest('.player-item').classList.remove('selected');
    });

    const shuffled = checkboxes.sort(() => 0.5 - Math.random());
    const selected = shuffled.slice(0, 11);

    selected.forEach(cb => {
        cb.checked = true;
        cb.closest('.player-item').classList.add('selected');
    });
}

function filterPlayers(input) {
    const filter = input.value.toLowerCase();
    const list = input.nextElementSibling.querySelector('.player-list');
    const items = list.getElementsByTagName('label');

    for (let i = 0; i < items.length; i++) {
        const text = items[i].textContent || items[i].innerText;
        if (text.toLowerCase().indexOf(filter) > -1) {
            items[i].style.display = "";
        } else {
            items[i].style.display = "none";
        }
    }
}

function selectTossOption(teamId, decision, card) {
    document.getElementById('toss_winner_input').value = teamId;
    document.getElementById('toss_decision_input').value = decision;

    document.querySelectorAll('.toss-card').forEach(el => el.classList.remove('selected'));
    card.classList.add('selected');

    document.getElementById('toss-submit-btn').disabled = false;
}

// Highlight selected players on load and change
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.player-item input[name="player_ids[]"]').forEach(cb => {
        if (cb.checked) cb.closest('.player-item').classList.add('selected');

        cb.addEventListener('change', function () {
            if (this.checked) this.closest('.player-item').classList.add('selected');
            else this.closest('.player-item').classList.remove('selected');
        });
    });
});

function toggleCoinFlip() {
    const container = document.getElementById('coin-simulator');
    if (container.style.display === 'none' || !container.style.display) {
        container.style.display = 'block';
        // Reset to placeholder
        document.getElementById('coin-placeholder').style.display = 'flex';
        document.getElementById('coin').style.display = 'none';
        document.getElementById('coin-result').textContent = '';
    } else {
        container.style.display = 'none';
    }
}

function flipCoin() {
    vibrateOnTap(50); // Added haptic
    const coin = document.getElementById('coin');
    const placeholder = document.getElementById('coin-placeholder');
    const resultDiv = document.getElementById('coin-result');

    // Hide placeholder, show coin
    placeholder.style.display = 'none';
    coin.style.display = 'block';

    // Reset
    coin.style.transition = 'none';
    coin.style.transform = 'rotateY(0)';
    resultDiv.textContent = 'Flipping...';

    // Force reflow
    void coin.offsetWidth;

    // Start flip
    coin.style.transition = 'transform 3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';

    // Random outcome (0 = Heads, 1 = Tails)
    const isHeads = Math.random() < 0.5;
    const rotations = 5; // Number of full rotations
    const degrees = (rotations * 360) + (isHeads ? 0 : 180);

    coin.style.transform = `rotateY(${degrees}deg)`;

    setTimeout(() => {
        resultDiv.textContent = isHeads ? 'HEADS!' : 'TAILS!';
        resultDiv.style.color = isHeads ? '#d97706' : '#4b5563';
        vibrateOnTap(30); // Added haptic
    }, 3000);
}

// ============================================
// PHASE 1: CRITICAL UX IMPROVEMENTS
// ============================================

// 1. Haptic Feedback
function vibrateOnTap(duration = 10) {
    if ('vibrate' in navigator) {
        navigator.vibrate(duration);
    }
}

// 2. Toast Notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// 3. Loading States
function setLoadingState(button, loading) {
    if (loading) {
        button.classList.add('loading');
        button.disabled = true;
        button.setAttribute('data-original-text', button.textContent);
        button.textContent = 'Saving...';
    } else {
        button.classList.remove('loading');
        button.disabled = false;
        button.textContent = button.getAttribute('data-original-text') || 'Submit';
    }
}

// 4. Clear All Players
function clearAllPlayers(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.closest('.player-item')?.classList.remove('selected');
    });

    vibrateOnTap(15);
    showToast('All players cleared', 'info');
    saveDraft(formId);
}

// 5. Progress Persistence
function saveDraft(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const selected = Array.from(form.querySelectorAll('input[name="player_ids[]"]:checked'))
        .map(cb => cb.value);

    const guests = Array.from(form.querySelectorAll('input[name^="is_guest_"]:checked'))
        .map(cb => cb.name.replace('is_guest_', ''));

    localStorage.setItem(`draft_${formId}`, JSON.stringify({ selected, guests, timestamp: Date.now() }));
}

function restoreDraft(formId) {
    const saved = localStorage.getItem(`draft_${formId}`);
    if (!saved) return;

    try {
        const draft = JSON.parse(saved);
        const form = document.getElementById(formId);

        draft.selected.forEach(playerId => {
            const checkbox = form.querySelector(`input[value="${playerId}"]`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.closest('.player-item')?.classList.add('selected');
            }
        });

        draft.guests.forEach(playerId => {
            const guestCheckbox = form.querySelector(`input[name="is_guest_${playerId}"]`);
            if (guestCheckbox) guestCheckbox.checked = true;
        });

        showToast('Draft restored', 'info');
    } catch (e) {
        console.error('Failed to restore draft:', e);
    }
}

// 6. Error Recovery
async function submitWithRetry(form, retries = 3) {
    const formData = new FormData(form);

    for (let i = 0; i < retries; i++) {
        try {
            const response = await fetch(form.action || window.location.href, {
                method: 'POST',
                body: formData
            });

            if (response.ok) return { success: true };
        } catch (error) {
            if (i === retries - 1) {
                showToast('Failed to save. Please try again.', 'error');
                return { success: false };
            }
            await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
            showToast(`Retrying... (${i + 2}/${retries})`, 'warning');
        }
    }
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Add haptic feedback
    document.querySelectorAll('.toss-card, .player-item, .btn').forEach(el => {
        el.addEventListener('click', () => vibrateOnTap());
    });

    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => vibrateOnTap(5));
    });

    // Add loading states to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) setLoadingState(submitBtn, true);
        });
    });

    // Restore drafts and add auto-save
    document.querySelectorAll('form[id^="form-team-"]').forEach(form => {
        restoreDraft(form.id);
        form.addEventListener('change', () => saveDraft(form.id));
    });

    // Keyboard navigation for player items
    document.querySelectorAll('.player-item').forEach(item => {
        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            }
        });
    });
});
