import { useState, useEffect } from 'react'
import CreateMatchForm from './CreateMatchForm'

function MatchesList() {
    const [matches, setMatches] = useState([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)
    const [showCreateForm, setShowCreateForm] = useState(false)

    useEffect(() => {
        fetchMatches()
    }, [])

    const fetchMatches = () => {
        setLoading(true)
        fetch('/api/v1/matches.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setMatches(data.data)
                } else {
                    setError(data.error || 'Failed to fetch matches')
                }
                setLoading(false)
            })
            .catch(err => {
                setError(err.message)
                setLoading(false)
            })
    }

    const handleDelete = (id) => {
        if (!confirm('Are you sure you want to delete this match?')) return;

        fetch(`/api/v1/matches/${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    fetchMatches(); // Reload list
                } else {
                    alert('Failed to delete: ' + data.error);
                }
            })
    }

    const handleMatchCreated = () => {
        setShowCreateForm(false);
        fetchMatches();
    }

    if (loading && !matches.length) return <div>Loading matches...</div>

    return (
        <div className="matches-list-container">
            <div className="list-header">
                <h2>Manage Matches</h2>
                {!showCreateForm && (
                    <button className="btn-primary" onClick={() => setShowCreateForm(true)}>+ New Match</button>
                )}
            </div>

            {showCreateForm && (
                <CreateMatchForm
                    onMatchCreated={handleMatchCreated}
                    onCancel={() => setShowCreateForm(false)}
                />
            )}

            <table className="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Teams</th>
                        <th>Series</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {matches.map(item => {
                        const match = item.match || item;
                        return (
                            <tr key={match.match_id}>
                                <td>{new Date(match.match_date).toLocaleDateString()}</td>
                                <td><strong>{match.team1_name}</strong> vs <strong>{match.team2_name}</strong></td>
                                <td>{match.series_name}</td>
                                <td><span className={`status-badge ${match.state}`}>{match.state}</span></td>
                                <td>
                                    <button className="btn-sm" onClick={() => window.open(`scorer/${match.match_id}`, '_self')}>Score</button>
                                    <button className="btn-sm">Edit</button>
                                    <button className="btn-sm btn-danger" onClick={() => handleDelete(match.match_id)}>Delete</button>
                                </td>
                            </tr>
                        )
                    })}
                </tbody>
            </table>
        </div>
    )
}

export default MatchesList
