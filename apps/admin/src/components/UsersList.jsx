import { useState, useEffect } from 'react'

function UsersList() {
    const [users, setUsers] = useState([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)

    useEffect(() => {
        fetch('/api/v1/users.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setUsers(data.data)
                } else {
                    setError('Unauthorized or Failed')
                }
                setLoading(false)
            })
            .catch(err => {
                setError(err.message)
                setLoading(false)
            })
    }, [])

    if (loading) return <div>Loading Users...</div>
    if (error) return <div className="error">{error}</div>

    return (
        <div className="matches-list-container">
            <div className="list-header">
                <h2>Manage Users</h2>
                <button className="btn-primary" onClick={() => alert('Coming Soon')}>+ New User</button>
            </div>

            <table className="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map(u => (
                        <tr key={u.user_id}>
                            <td style={{ fontWeight: 600 }}>{u.username}</td>
                            <td><span className="status-badge">{u.role}</span></td>
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

export default UsersList
