import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import PlayerSelectModal from '../components/PlayerSelectModal'
import '../components/Modal.css'
import './Scorer.css'

function Scorer() {
    const { matchId } = useParams()
    const [matchData, setMatchData] = useState(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)

    // Game State
    const [striker, setStriker] = useState(null)
    const [nonStriker, setNonStriker] = useState(null)
    const [bowler, setBowler] = useState(null)
    const [showPlayerSelect, setShowPlayerSelect] = useState(false)
    const [serverSeq, setServerSeq] = useState(0)
    const [isSubmitting, setIsSubmitting] = useState(false)

    useEffect(() => {
        fetchMatch()
    }, [matchId])

    const fetchMatch = () => {
        fetch(`/api/v1/matches/${matchId}?include_scores=true&include_details=true`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setMatchData(data.data)
                    // Update sequence from match data
                    if (data.data.match.last_seq) {
                        setServerSeq(parseInt(data.data.match.last_seq))
                    }

                    // If we don't have players selected, check local state or prompt
                    if (!striker && !nonStriker && !bowler) {
                        // In a real app we'd parse the 'last event' or 'current state' from API
                        // For now, simpler: just prompt if empty
                        setShowPlayerSelect(true)
                    }
                } else {
                    setError('Failed to load match')
                }
                setLoading(false)
            })
            .catch(err => {
                setError(err.message)
                setLoading(false)
            })
    }

    const handlePlayerSelect = (players) => {
        setStriker(players.striker)
        setNonStriker(players.nonStriker)
        setBowler(players.bowler)
        setShowPlayerSelect(false)
    }

    const submitEvent = (payload) => {
        if (!striker || !nonStriker || !bowler) {
            alert("Please select players first!");
            setShowPlayerSelect(true);
            return;
        }

        setIsSubmitting(true);

        // Prepare event object
        // NOTE: We rely on the backend to resolve appearance_ids from these player IDs
        const eventData = {
            match_id: parseInt(matchId),
            events: [{
                event_uuid: crypto.randomUUID(),
                payload_json: {
                    ...payload,
                    striker_id: striker.player_id,
                    non_striker_id: nonStriker.player_id,
                    bowler_id: bowler.player_id,
                    innings: matchData.match.current_innings || 1
                }
            }],
            client_base_seq: serverSeq
        };

        fetch('/api/v1/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        })
            .then(res => res.json())
            .then(data => {
                setIsSubmitting(false);
                if (data.success) {
                    // Update sequence
                    setServerSeq(data.server_seq);
                    // Refresh match data to calculations (runs, overs etc)
                    // In a perfect world, we update local state optimistically.
                    fetchMatch();

                    // Handle rotation for odd runs (1, 3)
                    if (payload.runs % 2 !== 0 && payload.type === 'run') {
                        // Swap striker/non-striker
                        const temp = striker;
                        setStriker(nonStriker);
                        setNonStriker(temp);
                    }

                    // Handle Wicket: Reset striker
                    if (payload.type === 'wicket') {
                        setStriker(null);
                        // Don't show modal immediately, let user see the generic state? 
                        // Actually, let's just pop the modal to pick new batter
                        setShowPlayerSelect(true);
                    }
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                    // If sequence conflict, fetch match again
                    if (data.error === 'Sequence conflict') {
                        fetchMatch();
                    }
                }
            })
            .catch(err => {
                setIsSubmitting(false);
                alert('Network error: ' + err.message);
            });
    }

    const handleRun = (runs) => {
        submitEvent({
            type: 'run',
            runs: runs,
            is_legal: true
        });
    }

    const handleExtra = (type) => {
        // Simple Wide/NB implementation
        submitEvent({
            type: 'extra',
            extra_type: type, // 'wide' or 'no_ball'
            runs: 1, // Standard 1 run penalty
            is_legal: false
        });
    }

    const handleWicket = () => {
        if (!confirm("Confirm Wicket?")) return;
        submitEvent({
            type: 'wicket',
            wicket_type: 'bowled', // Default, should have a modal for this
            runs: 0,
            is_legal: true
        });
    }

    if (loading) return <div className="scorer-loading">Loading Scorer...</div>
    if (error) return <div className="scorer-error">{error}</div>
    if (!matchData) return <div>Match not found</div>

    const { match, score } = matchData

    return (
        <div className="scorer-container">
            <header className="scorer-header">
                <Link to="/" className="back-btn">← Exit</Link>
                <div className="match-info-tiny">
                    {match.team1_name} vs {match.team2_name}
                </div>
                <div className="live-indicator">LIVE</div>
            </header>

            {showPlayerSelect && (
                <PlayerSelectModal
                    match={match}
                    onSelect={handlePlayerSelect}
                    onCancel={() => setShowPlayerSelect(false)}
                />
            )}

            <div className="score-display-main">
                <div className="big-score">
                    {score?.innings1?.runs || 0}/{score?.innings1?.wickets || 0}
                </div>
                <div className="overs-display">
                    {score?.innings1 ? `(${score.innings1.overs} ov)` : '(0.0 ov)'}
                </div>
                <div className="crr-display">CRR: {score?.innings1?.run_rate || '0.00'}</div>
            </div>

            <div className="active-players-section">
                {/* Batsmen */}
                <div className={`player-card active-batsman ${!striker ? 'empty' : ''}`} onClick={() => setShowPlayerSelect(true)}>
                    <span className="p-role">Striker</span>
                    <span className="p-name">{striker?.name || 'Select Striker'}</span>
                    <span className="p-stats">
                        {/* We could calculate live stats here if we had event history */}
                        *
                    </span>
                </div>
                <div className="player-card">
                    <span className="p-role">Non-Striker</span>
                    <span className="p-name">{nonStriker?.name || 'Select Non-Striker'}</span>
                </div>

                {/* Bowler */}
                <div className="player-card bowler-card" onClick={() => setShowPlayerSelect(true)}>
                    <span className="p-role">Bowler</span>
                    <span className="p-name">{bowler?.name || 'Select Bowler'}</span>
                </div>
            </div>

            <div className={`control-pad ${isSubmitting ? 'disabled' : ''}`}>
                <div className="row">
                    <button className="score-btn" onClick={() => handleRun(0)} disabled={isSubmitting}>0</button>
                    <button className="score-btn" onClick={() => handleRun(1)} disabled={isSubmitting}>1</button>
                    <button className="score-btn" onClick={() => handleRun(2)} disabled={isSubmitting}>2</button>
                    <button className="score-btn" onClick={() => handleRun(3)} disabled={isSubmitting}>3</button>
                </div>
                <div className="row">
                    <button className="score-btn four" onClick={() => handleRun(4)} disabled={isSubmitting}>4</button>
                    <button className="score-btn siix" onClick={() => handleRun(6)} disabled={isSubmitting}>6</button>
                    <button className="score-btn wide" onClick={() => handleExtra('wide')} disabled={isSubmitting}>WD</button>
                    <button className="score-btn noball" onClick={() => handleExtra('no_ball')} disabled={isSubmitting}>NB</button>
                </div>
                <div className="row">
                    <button className="score-btn wicket" onClick={handleWicket} disabled={isSubmitting}>OUT</button>
                    <button className="score-btn undo" disabled>UNDO</button>
                </div>
            </div>
        </div>
    )
}

export default Scorer
