import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import BottomNav from '../components/BottomNav'
import { getInningScore } from '../utils/cricket'

function Home() {
    const [liveMatches, setLiveMatches] = useState([])
    const [recentMatches, setRecentMatches] = useState([])
    const [upcomingMatches, setUpcomingMatches] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        fetch('/api/v1/matches.php?include_scores=true')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const allMatches = data.data

                    const live = allMatches.filter(m => m.match.state === 'live' || m.match.state === 'inprogress')
                    const recent = allMatches.filter(m => m.match.state === 'completed' || m.match.state === 'abandoned')
                        .sort((a, b) => new Date(b.match.match_date) - new Date(a.match.match_date))
                        .slice(0, 3)

                    const upcoming = allMatches.filter(m => m.match.state === 'scheduled' || m.match.state === 'upcoming' || m.match.state === 'draft')
                        .sort((a, b) => new Date(a.match.match_date) - new Date(b.match.match_date))
                        .slice(0, 10)

                    setLiveMatches(live)
                    setRecentMatches(recent)
                    setUpcomingMatches(upcoming)
                }
                setLoading(false)
            })
            .catch(err => {
                console.error(err)
                setLoading(false)
            })
    }, [])



    const formatDate = (dateString, format) => {
        const d = new Date(dateString)
        if (format === 'short') {
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        }
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
    }

    if (loading) return (
        <div style={{ padding: 20, textAlign: 'center' }}>
            <div className="skeleton skeleton-row" style={{ width: '60%', margin: '0 auto' }}></div>
            <div className="skeleton skeleton-row" style={{ height: 200, marginTop: 20 }}></div>
        </div>
    )

    return (
        <div className="app-shell">
            <header className="app-header">
                <div className="header-content">
                    <div className="flex-between" style={{ width: '100%' }}>
                        <div className="page-title">🏏 CricApp</div>
                    </div>
                </div>
            </header>

            <div className="container">

                {/* Live Matches */}
                {liveMatches.length > 0 && (
                    <>
                        <div className="section-title" style={{ marginTop: 0 }}>
                            <span>Live Matches</span>
                            <span className="badge badge-live">LIVE</span>
                        </div>
                        <div className="horizontal-scroll">
                            {liveMatches.map(item => {
                                const { match, score } = item
                                return (
                                    <Link to={`/match/${match.match_id}`} key={match.match_id} className="match-card-mini featured-match">
                                        <div className="flex-between mb-4 text-xs text-muted">
                                            <span style={{ color: 'rgba(255,255,255,0.9)', textDecoration: 'underline' }}>
                                                {match.series_name || 'Friendly Match'}
                                            </span>
                                            <span className="text-danger font-bold">● LIVE</span>
                                        </div>

                                        {/* Team 1 */}
                                        <div className="score-row">
                                            <div className="team-name">
                                                <span className="avatar">{match.team1_name.substring(0, 1)}</span>
                                                {match.team1_short_name || match.team1_name.substring(0, 3)}
                                            </div>
                                            <div className="team-score">
                                                {getInningScore(score, match.team1_id) || '-'}
                                            </div>
                                        </div>

                                        {/* Team 2 */}
                                        <div className="score-row">
                                            <div className="team-name">
                                                <span className="avatar">{match.team2_name.substring(0, 1)}</span>
                                                {match.team2_short_name || match.team2_name.substring(0, 3)}
                                            </div>
                                            <div className="team-score">
                                                {getInningScore(score, match.team2_id) || '-'}
                                            </div>
                                        </div>

                                        <div className="match-status-text" style={{ color: '#fbbf24' }}>
                                            {match.status_note || 'Match in progress'}
                                        </div>
                                    </Link>
                                )
                            })}
                        </div>
                    </>
                )}


                {/* Recent Results */}
                <div className="section-title">
                    <span>Recent Results</span>
                    <Link to="/matches?filter=results" className="view-all">View All</Link>
                </div>

                {recentMatches.length === 0 ? (
                    <div className="glass-card" style={{ padding: 32, textAlign: 'center', color: 'var(--text-muted)' }}>
                        No recent matches found.
                    </div>
                ) : (
                    recentMatches.map(item => {
                        const { match, score } = item
                        return (
                            <Link to={`/match/${match.match_id}`} key={match.match_id} className="glass-card" style={{ display: 'block' }}>
                                <div className="card-body">
                                    <div className="flex-between mb-2 text-xs text-muted">
                                        <span>{formatDate(match.match_date, 'short')}</span>
                                        <span>{match.series_name || 'Match'}</span>
                                    </div>

                                    <div className="score-row">
                                        <div className="team-name">{match.team1_name}</div>
                                        <div className="team-score">{getInningScore(score, match.team1_id)}</div>
                                    </div>

                                    <div className="score-row">
                                        <div className="team-name">{match.team2_name}</div>
                                        <div className="team-score">{getInningScore(score, match.team2_id)}</div>
                                    </div>

                                    <div className="text-xs text-primary font-bold mt-2">
                                        {match.winner_id ?
                                            (match.winner_id === match.team1_id ? `${match.team1_name} won` : `${match.team2_name} won`)
                                            : 'Match Completed'}
                                    </div>
                                </div>
                            </Link>
                        )
                    })
                )}


                {/* Upcoming */}
                <div className="section-title">
                    <span>Upcoming</span>
                    <Link to="/matches?filter=schedule" className="view-all">View All</Link>
                </div>

                {upcomingMatches.length === 0 ? (
                    <div className="glass-card" style={{ padding: 32, textAlign: 'center', color: 'var(--text-muted)' }}>
                        No upcoming matches scheduled.
                    </div>
                ) : (
                    <div className="horizontal-scroll">
                        {upcomingMatches.map(item => {
                            const { match } = item
                            return (
                                <div key={match.match_id} className="match-card-mini" style={{ minWidth: 240 }}>
                                    <div className="text-xs text-muted mb-2">{formatDate(match.match_date)}</div>
                                    <div className="font-bold mb-1 text-sm">{match.team1_name}</div>
                                    <div className="text-xs text-muted mb-1">vs</div>
                                    <div className="font-bold mb-2 text-sm">{match.team2_name}</div>
                                    <div className="text-xs text-muted">{match.venue || 'Venue TBD'}</div>
                                </div>
                            )
                        })}
                    </div>
                )}

            </div>

            <BottomNav />
        </div>
    )
}

export default Home
