/**
 * Cricket Scoring Engine
 * 
 * Modular scoring logic for cricket match ball-by-ball recording.
 * Handles runs, wickets, extras, over management, and statistics.
 */

class ScoringEngine {
    constructor(config) {
        this.matchId = config.matchId;
        this.currentInnings = config.currentInnings || 1;
        this.maxOvers = config.maxOvers || 20;
        this.firstInningsTotal = config.firstInningsTotal || 0;
        this.apiUrl = config.apiUrl || '/api/v1/events.php';

        // Match state
        this.currentScore = 0;
        this.currentWickets = 0;
        this.currentBalls = 0;
        this.currentOvers = 0;
        this.clientSeq = 0;
        this.serverSeq = 0;
        this.eventHistory = [];

        // Player stats
        this.playerStats = {
            batsmen: {},
            bowlers: {}
        };

        // Current players
        this.currentStrikerId = null;
        this.currentNonStrikerId = null;
        this.currentBowlerId = null;
        this.currentOverBalls = [];

        // Callbacks
        this.onScoreUpdate = config.onScoreUpdate || (() => { });
        this.onOverComplete = config.onOverComplete || (() => { });
        this.onInningsComplete = config.onInningsComplete || (() => { });
        this.onError = config.onError || ((error) => console.error(error));
    }

    /**
     * Helper: Check if ball is legal delivery
     */
    isLegalBall(type, extraType) {
        if (type === 'extra') {
            return !(extraType === 'wide' || extraType === 'no-ball');
        }
        return true;
    }

    /**
     * Helper: Determine if strike should rotate
     */
    shouldRotateStrike(runs, isNoBall) {
        return (runs % 2 === 1);
    }

    /**
     * Helper: Check if innings is complete
     */
    isInningsComplete(wickets, overs, maxOvers) {
        return wickets >= 10 || overs >= maxOvers;
    }

    /**
     * Helper: Calculate overs from balls
     */
    calculateOvers(balls) {
        const completedOvers = Math.floor(balls / 6);
        const remainingBalls = balls % 6;
        return completedOvers + (remainingBalls / 10);
    }

    /**
     * Helper: Calculate run rate
     */
    calculateRunRate(runs, balls) {
        if (balls === 0) return 0.0;
        return (runs / balls) * 6;
    }

    /**
     * Helper: Calculate target for second innings
     */
    calculateTarget(firstInningsTotal) {
        return firstInningsTotal + 1;
    }

    /**
     * Helper: Calculate required run rate
     */
    calculateRequiredRunRate(target, currentScore, remainingOvers) {
        const remainingRuns = target - currentScore;
        if (remainingRuns <= 0 || remainingOvers <= 0) {
            return 0.0;
        }
        return remainingRuns / remainingOvers;
    }

    /**
     * Set current players
     */
    setPlayers(strikerId, nonStrikerId, bowlerId) {
        this.currentStrikerId = strikerId ? parseInt(strikerId) : null;
        this.currentNonStrikerId = nonStrikerId ? parseInt(nonStrikerId) : null;
        this.currentBowlerId = bowlerId ? parseInt(bowlerId) : null;

        // Initialize stats if needed
        if (this.currentStrikerId && !this.playerStats.batsmen[this.currentStrikerId]) {
            this.playerStats.batsmen[this.currentStrikerId] = { runs: 0, balls: 0, fours: 0, sixes: 0 };
        }
        if (this.currentNonStrikerId && !this.playerStats.batsmen[this.currentNonStrikerId]) {
            this.playerStats.batsmen[this.currentNonStrikerId] = { runs: 0, balls: 0, fours: 0, sixes: 0 };
        }
        if (this.currentBowlerId && !this.playerStats.bowlers[this.currentBowlerId]) {
            this.playerStats.bowlers[this.currentBowlerId] = { runs: 0, balls: 0, wickets: 0, overs: 0 };
        }
    }

    /**
     * Record a ball event (run, wicket, or extra)
     */
    async recordBall(data) {
        if (!this.currentStrikerId || !this.currentBowlerId) {
            this.onError('Please select striker and bowler');
            return false;
        }

        // Store event for undo
        const eventData = {
            type: data.type,
            runs: data.runs || 0,
            striker_id: this.currentStrikerId,
            bowler_id: this.currentBowlerId,
            extra_type: data.extra_type || null,
            dismissal_type: data.dismissal_type || null,
            timestamp: Date.now()
        };
        this.eventHistory.push(eventData);

        // Create event payload
        const event = {
            event_uuid: this.generateUUID(),
            client_id: 'web-scorer-' + Date.now(),
            client_ts: new Date().toISOString(),
            client_base_seq: this.serverSeq,
            ball_index: this.currentBalls,
            appearance_id: null,
            payload_json: {
                type: data.type,
                runs: data.runs || 0,
                striker_id: this.currentStrikerId,
                bowler_id: this.currentBowlerId,
                extra_type: data.extra_type || null,
                dismissal_type: data.dismissal_type || null,
                innings: this.currentInnings
            }
        };

        try {
            // Send to API
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    match_id: this.matchId,
                    client_base_seq: this.serverSeq,
                    events: [event]
                })
            });

            const result = await response.json();

            if (result.success) {
                this.serverSeq = result.server_seq;
                this.clientSeq++;

                // Update local state
                this.updateStatsAfterBall(data);

                // Check for over completion
                const isLegalDelivery = this.isLegalBall(data.type, data.extra_type);
                if (isLegalDelivery && this.currentBalls % 6 === 0 && this.currentBalls > 0) {
                    this.currentOvers++;
                    this.currentBalls = 0;
                    this.currentOverBalls = [];
                    this.swapStrike();
                    this.onOverComplete(this.currentOvers);
                }

                // Check for innings completion
                if (this.isInningsComplete(this.currentWickets, this.currentOvers, this.maxOvers)) {
                    this.onInningsComplete();
                }

                this.onScoreUpdate(this.getScoreData());

                return true;
            } else {
                this.onError('Error: ' + (result.error || 'Failed to record ball'));
                return false;
            }
        } catch (error) {
            this.onError('Failed to record ball: ' + error.message);
            return false;
        }
    }

    /**
     * Update statistics after ball is recorded
     */
    updateStatsAfterBall(data) {
        if (data.type === 'run') {
            this.currentScore += data.runs || 0;
            this.currentBalls++;

            // Update batsman stats
            if (this.playerStats.batsmen[this.currentStrikerId]) {
                this.playerStats.batsmen[this.currentStrikerId].runs += data.runs || 0;
                this.playerStats.batsmen[this.currentStrikerId].balls++;
                if (data.runs === 4) this.playerStats.batsmen[this.currentStrikerId].fours++;
                if (data.runs === 6) this.playerStats.batsmen[this.currentStrikerId].sixes++;
            }

            // Update bowler stats
            if (this.playerStats.bowlers[this.currentBowlerId]) {
                this.playerStats.bowlers[this.currentBowlerId].runs += data.runs || 0;
                this.playerStats.bowlers[this.currentBowlerId].balls++;
            }

            // Handle strike rotation
            if (this.shouldRotateStrike(data.runs || 0, false)) {
                this.swapStrike();
            }
        } else if (data.type === 'wicket') {
            this.currentWickets++;
            this.currentBalls++;

            if (this.playerStats.batsmen[this.currentStrikerId]) {
                this.playerStats.batsmen[this.currentStrikerId].balls++;
            }
            if (this.playerStats.bowlers[this.currentBowlerId]) {
                this.playerStats.bowlers[this.currentBowlerId].wickets++;
                this.playerStats.bowlers[this.currentBowlerId].balls++;
            }
            // New batsman comes in - keep strike
        } else if (data.type === 'extra') {
            const extraRuns = data.runs || 1;
            this.currentScore += extraRuns;

            if (this.playerStats.bowlers[this.currentBowlerId]) {
                this.playerStats.bowlers[this.currentBowlerId].runs += extraRuns;
            }

            // Only count as ball if legal delivery
            if (this.isLegalBall('extra', data.extra_type)) {
                this.currentBalls++;
                if (this.playerStats.bowlers[this.currentBowlerId]) {
                    this.playerStats.bowlers[this.currentBowlerId].balls++;
                }
            }
        }
    }

    /**
     * Swap striker and non-striker
     */
    swapStrike() {
        const temp = this.currentStrikerId;
        this.currentStrikerId = this.currentNonStrikerId;
        this.currentNonStrikerId = temp;
    }

    /**
     * Get current score data
     */
    getScoreData() {
        const totalBalls = this.currentOvers * 6 + this.currentBalls;
        const oversDisplay = this.calculateOvers(totalBalls);
        const runRate = this.calculateRunRate(this.currentScore, totalBalls);

        let target = null;
        let requiredRR = null;

        if (this.currentInnings === 2 && this.firstInningsTotal > 0) {
            target = this.calculateTarget(this.firstInningsTotal);
            const remainingOvers = this.maxOvers - (this.currentOvers + this.currentBalls / 6);
            requiredRR = this.calculateRequiredRunRate(target, this.currentScore, remainingOvers);
        }

        return {
            score: this.currentScore,
            wickets: this.currentWickets,
            overs: this.currentOvers,
            balls: this.currentBalls,
            oversDisplay: oversDisplay,
            runRate: runRate,
            target: target,
            requiredRR: requiredRR,
            playerStats: this.playerStats
        };
    }

    /**
     * Load match state from server
     */
    async loadMatchState() {
        // This would be implemented to sync with server
        // For now, placeholder
        return true;
    }

    /**
     * Undo last ball
     */
    undoLastBall() {
        if (this.eventHistory.length === 0) {
            return false;
        }

        const lastEvent = this.eventHistory.pop();

        // Reverse stats (simplified - would need full implementation)
        if (lastEvent.type === 'run') {
            this.currentScore -= lastEvent.runs || 0;
            this.currentBalls--;
            // Reverse player stats...
        } else if (lastEvent.type === 'wicket') {
            this.currentWickets--;
            this.currentBalls--;
        } else if (lastEvent.type === 'extra') {
            const extraRuns = lastEvent.runs || 1;
            this.currentScore -= extraRuns;
            if (this.isLegalBall('extra', lastEvent.extra_type)) {
                this.currentBalls--;
            }
        }

        // Adjust overs if needed
        if (this.currentBalls < 0) {
            this.currentOvers--;
            this.currentBalls = 5;
        }

        this.onScoreUpdate(this.getScoreData());
        return true;
    }

    /**
     * Generate UUID
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
}

// Export for use in modules or global scope
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScoringEngine;
} else {
    window.ScoringEngine = ScoringEngine;
}








