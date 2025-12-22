import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import Scorecard from '../components/Scorecard'
import { getInningScore } from '../utils/cricket'


function MatchDetail() {
    const { id } = useParams()
    const [matchData, setMatchData] = useState(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)
    const [activeTab, setActiveTab] = useState('scorecard')

    useEffect(() => {
        const fetchMatch = () => {
            fetch(`/api/v1/matches/${id}?include_details=true`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setMatchData(data.data)
                    } else {
                        setError(data.error || 'Failed to fetch match')
                    }
                    setLoading(false)
                })
                .catch(err => {
                    setError(err.message)
                    setLoading(false)
                })
        }

        fetchMatch()
        // Poll every 30 seconds for live data
        const interval = setInterval(fetchMatch, 30000)
        return () => clearInterval(interval)
    }, [id])

    if (loading) return (
        <div style={{ padding: 20 }}>
            <div className="skeleton skeleton-row" style={{ height: 200 }}></div>
            <div className="skeleton skeleton-row" style={{ height: 50, marginTop: 20 }}></div>
        </div>
    )
    if (error) return <div className="container p-4 text-center text-danger">Error: {error} <br /> <Link to="/" className="text-primary underline">Go Home</Link></div>
    if (!matchData) return <div className="container p-4 text-center text-danger">Match not found</div>

    const { match, score, inningsData, teamPlayers } = matchData

    const renderSquads = () => {
        if (!teamPlayers) return <div className="p-4 text-muted">Squad information not available</div>

        return (
            <div className="squads-container">
                <div className="team-squad glass-card">
                    <div className="card-header">{match.team1_name}</div>
                    <div className="card-body p-0">
                        {teamPlayers[match.team1_id]?.map((p, i) => (
                            <div key={p.player_id} className="list-item">
                                <span className="avatar" style={{ width: 30, height: 30, fontSize: 10 }}>{p.name.substring(0, 1)}</span>
                                <div style={{ flex: 1 }}>
                                    <div className="font-bold text-sm">{p.name}</div>
                                    <div className="text-xs text-muted">{p.role || 'Player'}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="team-squad glass-card">
                    <div className="card-header">{match.team2_name}</div>
                    <div className="card-body p-0">
                        {teamPlayers[match.team2_id]?.map((p, i) => (
                            <div key={p.player_id} className="list-item">
                                <span className="avatar" style={{ width: 30, height: 30, fontSize: 10 }}>{p.name.substring(0, 1)}</span>
                                <div style={{ flex: 1 }}>
                                    <div className="font-bold text-sm">{p.name}</div>
                                    <div className="text-xs text-muted">{p.role || 'Player'}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        )
    }

    return (
        <div className="app-shell" style={{ paddingBottom: 80 }}>
            <header className="app-header">
                <div className="header-content">
                    <div className="flex-between" style={{ width: '100%' }}>
                        <Link to="/matches" style={{ color: 'white', marginRight: 10 }}>←</Link>
                        <div className="page-title" style={{ fontSize: 16 }}>{match.series_name}</div>
                        <div style={{ width: 24 }}></div>
                    </div>
                </div>
            </header>

            <div className="container">

                {/* Match Header Card */}
                <div className="glass-card mb-4" style={{ background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)', color: 'white' }}>
                    <div className="card-body text-center">
                        <div className="text-xs mb-4" style={{ opacity: 0.7 }}>
                            {new Date(match.match_date).toLocaleDateString()} • {match.venue}
                        </div>

                        <div className="score-board">
                            <div className="team-block text-right">
                                <div className="team-name-lg text-white">{match.team1_short_name || match.team1_name}</div>
                                <div className="score-lg text-white">
                                    {getInningScore(score, match.team1_id).split(' ')[0] || '0/0'}
                                </div>
                                <div className="overs-lg" style={{ color: '#94a3b8' }}>
                                    {getInningScore(score, match.team1_id).split(' ')[1] || '(0.0)'}
                                </div>
                            </div>

                            <div className="vs-badge">VS</div>

                            <div className="team-block text-left">
                                <div className="team-name-lg text-white">{match.team2_short_name || match.team2_name}</div>
                                <div className="score-lg text-white">
                                    {getInningScore(score, match.team2_id).split(' ')[0] || '0/0'}
                                </div>
                                <div className="overs-lg" style={{ color: '#94a3b8' }}>
                                    {getInningScore(score, match.team2_id).split(' ')[1] || '(0.0)'}
                                </div>
                            </div>
                        </div>

                        <div className="mt-4 text-sm font-bold" style={{ color: '#fbbf24' }}>
                            {match.status_note || match.state}
                        </div>
                    </div>
                </div>

                {/* Tabs */}
                <div className="nav-tabs-scroll mb-4">
                    <button className={`nav-tab-link ${activeTab === 'scorecard' ? 'active' : ''}`} onClick={() => setActiveTab('scorecard')}>Scorecard</button>
                    <button className={`nav-tab-link ${activeTab === 'squad' ? 'active' : ''}`} onClick={() => setActiveTab('squad')}>Squads</button>
                    <button className={`nav-tab-link ${activeTab === 'info' ? 'active' : ''}`} onClick={() => setActiveTab('info')}>Info</button>
                </div>

                <div className="tab-content">
                    {activeTab === 'scorecard' && (
                        inningsData ? <Scorecard data={inningsData} match={match} /> : <div className="glass-card p-4 text-center text-muted">Scorecard unavailable</div>
                    )}

                    {activeTab === 'squad' && renderSquads()}

                    {activeTab === 'info' && (
                        <div className="glass-card">
                            <div className="card-header">Match Information</div>
                            <div className="card-body">
                                <div className="premium-table">
                                    <div className="list-item">
                                        <div style={{ width: 120, color: 'var(--text-muted)' }}>Series</div>
                                        <div className="font-bold">{match.series_name}</div>
                                    </div>
                                    <div className="list-item">
                                        <div style={{ width: 120, color: 'var(--text-muted)' }}>Date</div>
                                        <div className="font-bold">{new Date(match.match_date).toLocaleString()}</div>
                                    </div>
                                    <div className="list-item">
                                        <div style={{ width: 120, color: 'var(--text-muted)' }}>Venue</div>
                                        <div className="font-bold">{match.venue}</div>
                                    </div>
                                    <div className="list-item">
                                        <div style={{ width: 120, color: 'var(--text-muted)' }}>Toss</div>
                                        <div>
                                            {match.toss_decision
                                                ? <span><b>{match.toss_winner_id === match.team1_id ? match.team1_name : match.team2_name}</b> opted to <b>{match.toss_decision}</b></span>
                                                : '-'}
                                        </div>
                                    </div>
                                    <div className="list-item">
                                        <div style={{ width: 120, color: 'var(--text-muted)' }}>Status</div>
                                        <div className="badge badge-primary">{match.state}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    )
}

export default MatchDetail
