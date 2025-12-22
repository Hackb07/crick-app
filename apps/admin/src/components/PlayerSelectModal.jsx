import { useState, useEffect } from 'react'

function PlayerSelectModal({ match, onSelect, onCancel }) {
    const [players, setPlayers] = useState([])
    const [loading, setLoading] = useState(true)
    const [selection, setSelection] = useState({
        strikerId: '',
        nonStrikerId: '',
        bowlerId: ''
    })

    useEffect(() => {
        // Fetch squads including all previous players or current match appearances
        // In a real app we would have a dedicated endpoint for "available players for selection"
        // For now, we'll try to get all players or specific team players if possible
        fetch(`/api/v1/matches/${match.match_id}?include_details=true`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Flatten squads for selection
                    const allPlayers = []
                    const tp = data.data.teamPlayers || {}
                    if (tp[match.team1_id]) allPlayers.push(...tp[match.team1_id])
                    if (tp[match.team2_id]) allPlayers.push(...tp[match.team2_id])

                    setPlayers(allPlayers)
                }
                setLoading(false)
            })
    }, [match])

    const handleSubmit = (e) => {
        e.preventDefault()
        // Determine names
        const striker = players.find(p => p.player_id == selection.strikerId)
        const nonStriker = players.find(p => p.player_id == selection.nonStrikerId)
        const bowler = players.find(p => p.player_id == selection.bowlerId)

        onSelect({
            striker, nonStriker, bowler
        })
    }

    if (loading) return <div className="modal-overlay">Loading players...</div>

    return (
        <div className="modal-overlay">
            <div className="modal-content">
                <h3>Select Players</h3>
                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label>Striker</label>
                        <select
                            value={selection.strikerId}
                            onChange={e => setSelection({ ...selection, strikerId: e.target.value })}
                            required
                        >
                            <option value="">Select Striker</option>
                            {players.map(p => (
                                <option key={p.player_id} value={p.player_id}>{p.name}</option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label>Non-Striker</label>
                        <select
                            value={selection.nonStrikerId}
                            onChange={e => setSelection({ ...selection, nonStrikerId: e.target.value })}
                            required
                        >
                            <option value="">Select Non-Striker</option>
                            {players.map(p => (
                                <option key={p.player_id} value={p.player_id} disabled={p.player_id == selection.strikerId}>{p.name}</option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label>Bowler</label>
                        <select
                            value={selection.bowlerId}
                            onChange={e => setSelection({ ...selection, bowlerId: e.target.value })}
                            required
                        >
                            <option value="">Select Bowler</option>
                            {players.map(p => (
                                <option key={p.player_id} value={p.player_id}>{p.name}</option>
                            ))}
                        </select>
                    </div>

                    <div className="modal-actions">
                        <button type="button" onClick={onCancel}>Cancel</button>
                        <button type="submit" className="btn-primary">Start Scoring</button>
                    </div>
                </form>
            </div>
        </div>
    )
}

export default PlayerSelectModal
