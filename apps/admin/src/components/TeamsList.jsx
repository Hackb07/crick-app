import { useState, useEffect } from 'react'

function TeamsList() {
    const [teams, setTeams] = useState([])
    const [loading, setLoading] = useState(true)
    const [showForm, setShowForm] = useState(false)
    const [formData, setFormData] = useState({ name: '', short_name: '', logo_url: '' })

    useEffect(() => {
        fetchTeams()
    }, [])

    const fetchTeams = () => {
        fetch('/api/v1/teams.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) setTeams(data.data)
                setLoading(false)
            })
    }

    const handleSubmit = (e) => {
        e.preventDefault()
        fetch('/api/v1/teams.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setShowForm(false)
                    setFormData({ name: '', short_name: '', logo_url: '' })
                    fetchTeams()
                } else {
                    alert(data.error)
                }
            })
    }

    if (loading) return <div>Loading Teams...</div>

    return (
        <div className="matches-list-container">
            <div className="list-header">
                <h2>Manage Teams</h2>
                {!showForm && <button className="btn-primary" onClick={() => setShowForm(true)}>+ New Team</button>}
            </div>

            {showForm && (
                <div className="create-match-form">
                    <h3>Create Team</h3>
                    <form onSubmit={handleSubmit} className="form-grid">
                        <div className="form-group">
                            <label>Team Name</label>
                            <input value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} required />
                        </div>
                        <div className="form-group">
                            <label>Short Name (3 chars)</label>
                            <input value={formData.short_name} onChange={e => setFormData({ ...formData, short_name: e.target.value })} required maxLength="4" />
                        </div>
                        <div className="form-group">
                            <label>Logo URL</label>
                            <input value={formData.logo_url} onChange={e => setFormData({ ...formData, logo_url: e.target.value })} />
                        </div>
                        <div className="form-actions">
                            <button type="button" className="btn-secondary" onClick={() => setShowForm(false)}>Cancel</button>
                            <button type="submit" className="btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            )}

            <table className="admin-table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Name</th>
                        <th>Short Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {teams.map(team => (
                        <tr key={team.team_id}>
                            <td>
                                {team.logo_url ? <img src={team.logo_url} alt="logo" style={{ width: 32, height: 32, borderRadius: '50%' }} /> : <div className="team-avatar-placeholder">{team.short_name.substring(0, 1)}</div>}
                            </td>
                            <td>{team.name}</td>
                            <td>{team.short_name}</td>
                            <td><button className="btn-sm">Edit</button></td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    )
}

export default TeamsList
