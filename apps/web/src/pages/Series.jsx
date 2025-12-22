import { useState, useEffect } from 'react'
import BottomNav from '../components/BottomNav'

function Series() {
    const [seriesData, setSeriesData] = useState({ active: [], upcoming: [], completed: [] })
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        fetch('/api/v1/series.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setSeriesData(data.data)
                }
                setLoading(false)
            })
            .catch(err => {
                console.error(err)
                setLoading(false)
            })
    }, [])

    // We render a section if it has items
    const renderSection = (title, items, showBadge = false) => {
        if (!items || items.length === 0) return null
        return (
            <>
                <div className="section-title" style={{ marginTop: title === 'Active Series' ? 0 : 20 }}>
                    <span>{title}</span>
                    {showBadge && <span className="badge badge-live">LIVE</span>}
                </div>
                {items.map(s => (
                    <div key={s.series_id} className="glass-card" style={{ marginBottom: 16 }}>
                        <div className="card-body">
                            <h3 style={{ margin: '0 0 8px 0', fontSize: '1.125rem', color: showBadge ? 'var(--primary)' : 'inherit' }}>
                                {s.name}
                            </h3>
                            {s.description && (
                                <p style={{ margin: '0 0 12px 0', color: 'var(--text-muted)', fontSize: '0.875rem' }}>
                                    {s.description}
                                </p>
                            )}
                            <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
                                <div style={{ fontSize: '0.875rem', color: 'var(--text-muted)' }}>
                                    📊 {s.match_count} matches
                                </div>
                                {s.completed_count > 0 && (
                                    <div style={{ fontSize: '0.875rem', color: 'var(--text-muted)' }}>
                                        ✅ {s.completed_count} completed
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </>
        )
    }

    if (loading) return (
        <div style={{ padding: 20, textAlign: 'center' }}>
            <div className="skeleton skeleton-row" style={{ width: '60%', margin: '0 auto' }}></div>
            <div className="skeleton skeleton-row" style={{ height: 200, marginTop: 20 }}></div>
        </div>
    )

    const hasAnySeries = seriesData.active.length > 0 || seriesData.upcoming.length > 0 || seriesData.completed.length > 0

    return (
        <div className="app-shell" style={{ paddingBottom: 80 }}>
            <header className="app-header">
                <div className="header-content">
                    <div className="page-title">🏆 Series</div>
                </div>
            </header>

            <div className="container">

                {renderSection('Active Series', seriesData.active, true)}
                {renderSection('Upcoming Series', seriesData.upcoming)}
                {renderSection('Completed Series', seriesData.completed)}

                {!hasAnySeries && (
                    <div className="glass-card" style={{ padding: 48, textAlign: 'center', color: 'var(--text-muted)' }}>
                        <div style={{ fontSize: '3rem', marginBottom: 16 }}>🏆</div>
                        <div style={{ fontSize: '1.125rem', fontWeight: 600, marginBottom: 8 }}>No Series Found</div>
                        <div style={{ fontSize: '0.875rem' }}>Series will appear here once they are created</div>
                    </div>
                )}
            </div>
            <BottomNav />
        </div>
    )
}

export default Series
