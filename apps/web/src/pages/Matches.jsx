import { useState, useEffect } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import BottomNav from '../components/BottomNav'
import { getInningScore } from '../utils/cricket'

function Matches() {
    const [matches, setMatches] = useState([])
    const [loading, setLoading] = useState(true)
    const [searchParams, setSearchParams] = useSearchParams()

    // Determine active filter from URL or default to 'all'
    const filter = searchParams.get('filter') || 'live'

    // We can map 'results' -> 'completed', 'schedule' -> 'scheduled' for API
    // checking logic inside:
    // API returns all matches, frontend filters. 
    // Ideally API should filter, but we are using existing /api/v1/matches.php which supports state

    useEffect(() => {
        setLoading(true)
        fetch('/api/v1/matches.php?include_scores=true')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let filtered = data.data

                    if (filter === 'live') {
                        filtered = filtered.filter(m => m.match.state === 'live' || m.match.state === 'inprogress')
                    } else if (filter === 'results') {
                        filtered = filtered.filter(m => m.match.state === 'completed' || m.match.state === 'abandoned')
                            .sort((a, b) => new Date(b.match.match_date) - new Date(a.match.match_date))
                    } else if (filter === 'schedule') {
                        filtered = filtered.filter(m => m.match.state === 'scheduled' || m.match.state === 'upcoming' || m.match.state === 'draft')
                            .sort((a, b) => new Date(a.match.match_date) - new Date(b.match.match_date))
                    }

                    setMatches(filtered)
                }
                setLoading(false)
            })
    }, [filter])



    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' })
    }

    return (
        <div className="app-shell" style={{ paddingBottom: 80 }}>
            <header className="app-header">
                <div className="header-content">
                    <div className="page-title">Matches</div>
                </div>
            </header>

            <div className="container">
                {/* Filter Tabs */}
                <div className="category-selector">
                    <button
                        className={`cat-btn ${filter === 'live' ? 'active' : ''}`}
                        onClick={() => setSearchParams({ filter: 'live' })}
                    >
                        Live
                    </button>
                    <button
                        className={`cat-btn ${filter === 'schedule' ? 'active' : ''}`}
                        onClick={() => setSearchParams({ filter: 'schedule' })}
                    >
                        Upcoming
                    </button>
                    <button
                        className={`cat-btn ${filter === 'results' ? 'active' : ''}`}
                        onClick={() => setSearchParams({ filter: 'results' })}
                    >
                        Results
                    </button>
                </div>

                {loading ? (
                    <div style={{ padding: 20, textAlign: 'center' }}>Loading...</div>
                ) : matches.length === 0 ? (
                    <div className="glass-card" style={{ padding: 40, textAlign: 'center', color: '#666' }}>
                        No matches found for this category.
                    </div>
                ) : (
                    <div className="matches-list">
                        {matches.map(({ match, score }) => (
                            <Link to={`/match/${match.match_id}`} key={match.match_id} className="glass-card" style={{ display: 'block' }}>
                                <div className="card-body">
                                    <div className="flex-between mb-2 text-xs text-muted">
                                        <span>{formatDate(match.match_date)}</span>
                                        <span>{match.series_name}</span>
                                    </div>

                                    {/* Team 1 */}
                                    <div className="score-row">
                                        <div className="team-name">
                                            <span className="avatar">{match.team1_name.substring(0, 1)}</span>
                                            {match.team1_short_name || match.team1_name}
                                        </div>
                                        <div className="team-score">
                                            {getInningScore(score, match.team1_id)}
                                        </div>
                                    </div>

                                    {/* Team 2 */}
                                    <div className="score-row">
                                        <div className="team-name">
                                            <span className="avatar">{match.team2_name.substring(0, 1)}</span>
                                            {match.team2_short_name || match.team2_name}
                                        </div>
                                        <div className="team-score">
                                            {getInningScore(score, match.team2_id)}
                                        </div>
                                    </div>

                                    <div className="match-status-text" style={{ textAlign: 'right', marginTop: 10 }}>
                                        {match.status_note || match.state}
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>

            <BottomNav />
        </div>
    )
}

export default Matches
