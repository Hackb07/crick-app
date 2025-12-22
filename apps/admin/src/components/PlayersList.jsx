import { useState, useEffect } from 'react'

function PlayersList() {
    const [players, setPlayers] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        fetch('/api/v1/players.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) setPlayers(data.data)
                setLoading(false)
            })
    }, [])

    if (loading) return <div>Loading Players...</div>

    return (
        <div className="matches-list-container">
            <div className="list-header">
                <h2>Manage Players</h2>
                <button className="btn-primary" onClick={() => alert('Add Player Coming Soon')}>+ New Player</button>
            </div>

            <table className="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Batting</th>
                        <th>Bowling</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {players.map(p => (
                        <tr key={p.player_id}>
                            <td style={{ fontWeight: 600 }}>{p.name}</td>
                            <td>{p.role || '-'}</td>
                            <td>{p.batting_hand}</td>
                            <td>{p.bowling_style}</td>
                            <td>
                                <button className="btn-sm">Edit</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    )
}

export default PlayersList
