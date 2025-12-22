import { useState, useEffect } from 'react'

function SeriesList() {
    const [series, setSeries] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        // We can reuse the options API for list or a dedicated series API
        // options.php?type=series returns basic info.
        // Let's assume we want more columns, we might need a dedicated endpoint or reuse options
        fetch('/api/v1/options.php?type=series')
            .then(res => res.json())
            .then(data => {
                if (data.success) setSeries(data.data)
                setLoading(false)
            })
    }, [])

    if (loading) return <div>Loading Series...</div>

    return (
        <div className="matches-list-container">
            <div className="list-header">
                <h2>Manage Series</h2>
                <button className="btn-primary" onClick={() => alert('Coming Soon')}>+ New Series</button>
            </div>

            <table className="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {series.map(s => (
                        <tr key={s.series_id}>
                            <td style={{ fontWeight: 600 }}>{s.name}</td>
                            <td><span className={`status-badge ${s.status}`}>{s.status}</span></td>
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

export default SeriesList
